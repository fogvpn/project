FROM php:8.2-cli

ENV DEBIAN_FRONTEND=noninteractive
ENV GOPATH=/root/go
ENV PATH="${GOPATH}/bin:/usr/local/go/bin:${PATH}"
ENV GO_VERSION=1.25.3
ENV GO111MODULE=on
ENV GO_PROXY=https://proxy.golang.org,direct

RUN apt-get update && apt-get install -y curl unzip jq git ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN curl -fsSL https://go.dev/dl/go${GO_VERSION}.linux-amd64.tar.gz -o /tmp/go.tar.gz \
    && tar -C /usr/local -xzf /tmp/go.tar.gz \
    && rm /tmp/go.tar.gz
RUN go version

WORKDIR /var/www/html
COPY . .

WORKDIR /var/www/html/cmd/xrayctl
RUN go mod init xrayctl || true \
 && go get github.com/xtls/xray-core@latest \
 && go get google.golang.org/grpc@latest \
 && go get google.golang.org/protobuf@latest \
 && go mod tidy \
 && go build -o /usr/local/bin/xrayctl .

WORKDIR /var/www/html/cmd/xraystats
RUN go mod init xraystats || true \
 && go get github.com/xtls/xray-core@latest \
 && go get google.golang.org/grpc@latest \
 && go mod tidy \
 && go build -o /usr/local/bin/xraystats .

RUN curl -L -o /tmp/xray.zip https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-64.zip \
    && unzip -o /tmp/xray.zip -d /usr/local/bin/ \
    && chmod +x /usr/local/bin/xray \
    && rm /tmp/xray.zip

WORKDIR /var/www/html
EXPOSE 80
CMD ["php", "-S", "0.0.0.0:80"]
