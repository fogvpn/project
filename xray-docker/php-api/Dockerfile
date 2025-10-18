FROM php:8.2-cli

ENV DEBIAN_FRONTEND=noninteractive
ENV GOPATH=/root/go
ENV PATH="${GOPATH}/bin:/usr/local/go/bin:${PATH}"
ENV GO_VERSION=1.23.2
ENV GO111MODULE=on
ENV GOPROXY=https://proxy.golang.org,direct

RUN apt-get update && apt-get install -y \
    curl unzip jq git build-essential protobuf-compiler wget ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://go.dev/dl/go${GO_VERSION}.linux-amd64.tar.gz -o /tmp/go.tar.gz \
    && tar -C /usr/local -xzf /tmp/go.tar.gz \
    && rm /tmp/go.tar.gz

RUN go version

RUN go install google.golang.org/protobuf/cmd/protoc-gen-go@v1.31.0 \
    && go install google.golang.org/grpc/cmd/protoc-gen-go-grpc@v1.4.0

RUN mkdir -p ${GOPATH}/src/github.com/xtls/xray-core
WORKDIR ${GOPATH}/src/github.com/xtls/xray-core

COPY proto ./ 

RUN set -eux; \
    protoc -I=. \
      app/proxyman/command/command.proto \
      app/proxyman/command/add_user_operation.proto \
      common/protocol/user.proto \
      common/serial/typed_message.proto \
      core/config.proto \
      --go_out=. --go-grpc_out=. \
      --go_opt=Mapp/proxyman/command/command.proto=github.com/xtls/xray-core/app/proxyman/command \
      --go_opt=Mapp/proxyman/command/add_user_operation.proto=github.com/xtls/xray-core/app/proxyman/command \
      --go_opt=Mcommon/protocol/user.proto=github.com/xtls/xray-core/common/protocol \
      --go_opt=Mcommon/serial/typed_message.proto=github.com/xtls/xray-core/common/serial \
      --go_opt=Mcore/config.proto=github.com/xtls/xray-core/core \
      --go-grpc_opt=Mapp/proxyman/command/command.proto=github.com/xtls/xray-core/app/proxyman/command \
      --go-grpc_opt=Mapp/proxyman/command/add_user_operation.proto=github.com/xtls/xray-core/app/proxyman/command \
      --go-grpc_opt=Mcommon/protocol/user.proto=github.com/xtls/xray-core/common/protocol \
      --go-grpc_opt=Mcommon/serial/typed_message.proto=github.com/xtls/xray-core/common/serial \
      --go-grpc_opt=Mcore/config.proto=github.com/xtls/xray-core/core

WORKDIR /var/www/html
COPY . .

WORKDIR /var/www/html/cmd/xrayctl
RUN go mod init xrayctl || true \
 && go get github.com/xtls/xray-core@latest \
 && go get google.golang.org/grpc@latest \
 && go get google.golang.org/protobuf@latest \
 && go mod tidy \
 && go build -o /usr/local/bin/xrayctl .

RUN curl -L -o /tmp/xray.zip https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-64.zip \
    && unzip -o /tmp/xray.zip -d /usr/local/bin/ \
    && chmod +x /usr/local/bin/xray \
    && rm /tmp/xray.zip

WORKDIR /var/www/html
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80"]
