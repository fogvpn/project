#!/bin/bash
set -e

CONFIG_TEMPLATE="/etc/xray/config.template.json"
CONFIG="/etc/xray/config.json"

: "${XRAY_PRIV:?Need to set XRAY_PRIV}"
: "${XRAY_PBK:?Need to set XRAY_PBK}"
: "${XRAY_SHORT_ID:?Need to set XRAY_SHORT_ID}"

envsubst < "$CONFIG_TEMPLATE" > "$CONFIG"

if ! jq empty "$CONFIG"; then
    echo "Invalid JSON in config"
    exit 1
fi

echo "[+] Starting Xray..."
exec /usr/bin/xray -c "$CONFIG"
