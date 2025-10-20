FOGVPN - Stay connected, even under strict restrictions. With open source code and the XRay protocol, you can access any resource quickly and securely
https://fogconfig.click/
###

xray-docker install:

apt update
apt upgrade -y
apt install -y curl wget git unzip jq bash lsb-release ca-certificates software-properties-common apt-transport-https
apt install jq

curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

sudo usermod -aG docker $USER

DOCKER_COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | jq -r '.tag_name')
sudo curl -L "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-linux-x86_64" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

cd /xray-docker
docker compose build
docker compose up -d
