FROM postgres
ENV API_PASSWORD=<your_password>
ENV ADMIN_PASSWORD=<your_password>
COPY ./initdb.d /home/db
RUN mv /home/db/setup.sh /docker-entrypoint-initdb.d