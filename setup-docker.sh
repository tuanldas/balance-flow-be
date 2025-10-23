#!/bin/bash

# Setup script for Balance Flow Docker environment
# This script creates the required external volumes and networks

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

PROJECT_NAME=${PROJECT_NAME:-balance-flow-be}

echo -e "${YELLOW}Setting up Docker environment for ${PROJECT_NAME}...${NC}"

# Create external volumes
echo -e "${YELLOW}Creating volumes...${NC}"

if docker volume ls | grep -q "${PROJECT_NAME}_database"; then
    echo -e "${GREEN}✓ Volume ${PROJECT_NAME}_database already exists${NC}"
else
    docker volume create "${PROJECT_NAME}_database"
    echo -e "${GREEN}✓ Created volume ${PROJECT_NAME}_database${NC}"
fi

if docker volume ls | grep -q "${PROJECT_NAME}_cache"; then
    echo -e "${GREEN}✓ Volume ${PROJECT_NAME}_cache already exists${NC}"
else
    docker volume create "${PROJECT_NAME}_cache"
    echo -e "${GREEN}✓ Created volume ${PROJECT_NAME}_cache${NC}"
fi

# Create external network
echo -e "${YELLOW}Creating network...${NC}"

if docker network ls | grep -q "${PROJECT_NAME}_external"; then
    echo -e "${GREEN}✓ Network ${PROJECT_NAME}_external already exists${NC}"
else
    docker network create "${PROJECT_NAME}_external"
    echo -e "${GREEN}✓ Created network ${PROJECT_NAME}_external${NC}"
fi

echo -e "${GREEN}✓ Docker environment setup complete!${NC}"
echo -e "${YELLOW}You can now run: docker-compose up -d${NC}"
