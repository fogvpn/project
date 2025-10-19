#!/bin/bash
set -e

CONFIG_TEMPLATE="/etc/xray/config.template.json"
CONFIG="/etc/xray/config.json"
USERS_FILE="/etc/xray/users.json"

: "${XRAY_PRIV:?Need to set XRAY_PRIV}"
: "${XRAY_PBK:?Need to set XRAY_PBK}"
: "${XRAY_SHORT_ID:?Need to set XRAY_SHORT_ID}"
: "${XRAY_SNI:?Need to set XRAY_SNI}"

envsubst < "$CONFIG_TEMPLATE" > "$CONFIG"

if [ -s "$USERS_FILE" ]; then
  CLIENTS_JSON=$(jq -c '[.[] | {id: .id, email: .email, flow: "xtls-rprx-vision"}]' "$USERS_FILE" 2>/dev/null || echo "[]")
else
  CLIENTS_JSON="[]"
fi

TMP="$(mktemp)"
jq --argjson clients "$CLIENTS_JSON" '
  .inbounds[0].settings.clients = $clients
' "$CONFIG" > "$TMP" && mv "$TMP" "$CONFIG"

if ! jq empty "$CONFIG" >/dev/null 2>&1; then
  echo "Invalid JSON in config"
  cat "$CONFIG"
  exit 1
fi

echo "[+] Starting Xray..."
exec /usr/bin/xray -c "$CONFIG"
