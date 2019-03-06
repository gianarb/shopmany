FROM gradle:5.2.1-jdk8
COPY . /opt/app
WORKDIR /opt/app
USER root
RUN gradle build --no-daemon --console plain
CMD ["java", "-jar","build/libs/pay-0.0.1.jar"]
