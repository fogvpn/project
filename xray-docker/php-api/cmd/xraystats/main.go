package main

import (
	"context"
	"encoding/json"
	"flag"
	"fmt"
	"log"
	"time"

	"google.golang.org/grpc"
	"google.golang.org/grpc/codes"
	"google.golang.org/grpc/credentials/insecure"
	"google.golang.org/grpc/status"

	statspb "github.com/xtls/xray-core/app/stats/command"
)

type Result struct {
	Email      string `json:"email"`
	UpBytes    int64  `json:"up_bytes"`
	DownBytes  int64  `json:"down_bytes"`
	TotalBytes int64  `json:"total_bytes"`
}

func fetch(c statspb.StatsServiceClient, name string, to time.Duration) (int64, error) {
	ctx, cancel := context.WithTimeout(context.Background(), to)
	defer cancel()
	resp, err := c.GetStats(ctx, &statspb.GetStatsRequest{ Name: name })
	if err != nil {
		if st, ok := status.FromError(err); ok && st.Code() == codes.NotFound {
			return 0, nil
		}
		return 0, err
	}
	if resp == nil || resp.Stat == nil {
		return 0, nil
	}
	return resp.Stat.Value, nil
}

func main() {
	email := flag.String("email", "", "user email used in xray")
	server := flag.String("server", "xray:10085", "xray gRPC address")
	timeout := flag.Int("timeout", 5, "timeout seconds")
	flag.Parse()

	if *email == "" {
		log.Fatal("email is required")
	}

	conn, err := grpc.Dial(*server, grpc.WithTransportCredentials(insecure.NewCredentials()))
	if err != nil {
		log.Fatalf("connect: %v", err)
	}
	defer conn.Close()

	c := statspb.NewStatsServiceClient(conn)
	to := time.Duration(*timeout) * time.Second

	up, err := fetch(c, fmt.Sprintf("user>>>%s>>>traffic>>>uplink", *email), to)
	if err != nil { log.Fatalf("get uplink: %v", err) }
	down, err := fetch(c, fmt.Sprintf("user>>>%s>>>traffic>>>downlink", *email), to)
	if err != nil { log.Fatalf("get downlink: %v", err) }

	out, _ := json.Marshal(Result{
		Email:      *email,
		UpBytes:    up,
		DownBytes:  down,
		TotalBytes: up + down,
	})
	fmt.Println(string(out))
}
