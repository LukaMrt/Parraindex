set shell := ["powershell", "-Command"]

image := "lukamrt/parraindex"

build-push:
    docker buildx build --platform linux/arm64 --push -t {{image}}:latest -f docker/Dockerfile .
