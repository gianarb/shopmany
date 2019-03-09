FROM openjdk:8-jdk-alpine
COPY . /opt/app
WORKDIR /opt/app
RUN apk add --update gradle
RUN gradle build
CMD ["java", "-jar","build/libs/pay-0.0.1.jar"]
