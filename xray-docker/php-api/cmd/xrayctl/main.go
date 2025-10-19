package main

import (
    "context"
    "encoding/json"
    "flag"
    "fmt"
    "log"
    "os"
    "time"

    "google.golang.org/grpc"
    "google.golang.org/grpc/credentials/insecure"
    "google.golang.org/protobuf/proto"

    commandpb "github.com/xtls/xray-core/app/proxyman/command"
    serialpb "github.com/xtls/xray-core/common/serial"
    protocolpb "github.com/xtls/xray-core/common/protocol"
    vlesspb "github.com/xtls/xray-core/proxy/vless"
)

func main() {
    tag := flag.String("tag", "vless", "inbound tag")
    id := flag.String("id", "", "user id (uuid format)")
    email := flag.String("email", "", "user email")
    server := flag.String("server", "xray:10085", "xray gRPC address")
    timeout := flag.Int("timeout", 5, "timeout seconds")
    flag.Parse()

    if *id == "" || *email == "" {
        fmt.Fprintln(os.Stderr, "id and email are required")
        os.Exit(2)
    }

    vlessAccount := &vlesspb.Account{
        Id:   *id,
        Flow: "xtls-rprx-vision",
    }

    accBytes, err := proto.Marshal(vlessAccount)
    if err != nil {
        log.Fatalf("marshal vless account: %v", err)
    }

    typedAccount := &serialpb.TypedMessage{
        Type:  "xray.proxy.vless.Account",
        Value: accBytes,
    }

    user := &protocolpb.User{
        Level: 0,
        Email: *email,
        Account: typedAccount,
    }

    addOp := &commandpb.AddUserOperation{
        User: user,
    }

    addOpBytes, err := proto.Marshal(addOp)
    if err != nil {
        log.Fatalf("marshal addOp: %v", err)
    }

    opTyped := &serialpb.TypedMessage{
        Type:  "xray.app.proxyman.command.AddUserOperation",
        Value: addOpBytes,
    }

    req := &commandpb.AlterInboundRequest{
        Tag:       *tag,
        Operation: opTyped,
    }

    ctx, cancel := context.WithTimeout(context.Background(), time.Duration(*timeout)*time.Second)
    defer cancel()

    conn, err := grpc.DialContext(ctx, *server, grpc.WithTransportCredentials(insecure.NewCredentials()), grpc.WithBlock())
    if err != nil {
        log.Fatalf("connect: %v", err)
    }
    defer conn.Close()

    client := commandpb.NewHandlerServiceClient(conn)

    _, err = client.AlterInbound(ctx, req)
    if err != nil {
        b, _ := json.Marshal(map[string]string{"error": err.Error()})
        fmt.Println(string(b))
        os.Exit(1)
    }

    fmt.Println(`{"result":"ok"}`)
    os.Exit(0)
}
