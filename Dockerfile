# Use Python 3.10 (compatible with pyodbc)
FROM python:3.10-slim

# Avoid prompts from apt
ENV DEBIAN_FRONTEND=noninteractive

# Install dependencies for pyodbc and SQL Server ODBC driver
RUN apt-get update && \
    apt-get install -y \
        gcc \
        g++ \
        curl \
        gnupg2 \
        unixodbc-dev \
        libpq-dev \
        build-essential \
        python3-dev \
        libssl-dev \
        libsasl2-dev \
        libldap2-dev \
        libsqlite3-dev \
        default-libmysqlclient-dev \
        wget \
        apt-transport-https \
        ca-certificates && \
    # Microsoft SQL Server ODBC Driver install
    curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - && \
    curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql17 && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy your code
COPY . .

# Install Python dependencies
RUN pip install --upgrade pip
RUN pip install -r requirements.txt

# Expose port (Flask default or your custom port)
EXPOSE 8000

# Run the Flask app with Gunicorn (adjust `app:app` as needed)
CMD ["gunicorn", "--bind", "0.0.0.0:8000", "app:app"]
