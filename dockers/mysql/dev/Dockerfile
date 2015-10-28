FROM ubuntu:trusty
MAINTAINER Fernando Mayo <fernando@tutum.co>, Feng Honglin <hfeng@tutum.co>

# Install packages
ENV DEBIAN_FRONTEND noninteractive
RUN apt-get update && \
  apt-get -yq install mysql-server-5.6 pwgen && \
  rm -rf /var/lib/apt/lists/*

# Remove pre-installed database
RUN rm -rf /var/lib/mysql/*

# Remove syslog configuration
RUN rm /etc/mysql/conf.d/mysqld_safe_syslog.cnf

# Add MySQL configuration
ADD docker_settings/my.cnf /etc/mysql/conf.d/my.cnf
ADD docker_settings/mysqld_charset.cnf /etc/mysql/conf.d/mysqld_charset.cnf

# Add MySQL scripts
ADD docker_settings/import_sql.sh /import_sql.sh
ADD docker_settings/run.sh /run.sh
ADD docker_settings/startup.sql /tmp/startup.sql
RUN chmod 755 /*.sh

# Exposed ENV
ENV MYSQL_USER **False**
ENV MYSQL_PASS **False**
ENV ON_CREATE_DB ~
ENV STARTUP_SQL /tmp/startup.sql

# Replication ENV
ENV REPLICATION_MASTER **False**
ENV REPLICATION_SLAVE **False**
ENV REPLICATION_USER replica
ENV REPLICATION_PASS replica

# Add VOLUMEs to allow backup of config and databases
VOLUME  ["/etc/mysql", "/var/lib/mysql"]

EXPOSE 3306
CMD ["/run.sh"]
