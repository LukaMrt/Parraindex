FROM ubuntu:latest
LABEL authors="nespo"

ENTRYPOINT ["top", "-b"]