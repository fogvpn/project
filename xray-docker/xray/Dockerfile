FROM alpine:latest

RUN apk add --no-cache bash curl unzip jq tzdata gettext \
    && curl -L -o /tmp/xray.zip https://github.com/XTLS/Xray-core/releases/latest/download/Xray-linux-64.zip \
    && unzip /tmp/xray.zip -d /usr/bin/ \
    && chmod +x /usr/bin/xray \
    && mkdir -p /etc/xray /var/log/xray

WORKDIR /etc/xray
COPY config.template.json /etc/xray/config.template.json
COPY entrypoint.sh /etc/xray/entrypoint.sh
RUN chmod +x /etc/xray/entrypoint.sh

ENTRYPOINT ["/etc/xray/entrypoint.sh"]
