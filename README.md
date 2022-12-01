<p align="center" style="justify-content: center; display:flex; align-items: center;">
    <a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="350"></a> 
    <!-- <font size="7" style="margin-right: 30px; padding-bottom: 20px">+</font> -->
    <a href="https://www.docker.com/" target="_blank"><img src="https://avatars.githubusercontent.com/u/7739233?s=200&v=4" width="200" height="150"></a>
</p>


## Note: With windows OS require install WSL2
## With docker:

### 1. Install Make:
- How to install Make on Linux: [Link](https://linuxhint.com/install-make-ubuntu)

    ```bash
        sudo apt update && sudo apt install make
    ```
- Check version:

    ```bash
        make -version
    ```

### 2. Install Docker:

- How to install Docker: [Link](https://www.docker.com/)
- Docker compose `docker/docker-compose.yml` declared 4 instances:

    ```bash
        - Web instance PHP 7.4.22
        - Database instance MySQL 5.7.35
        - Caching instance Redis 7.0.5
        - MailServer instance Mailhog 1.0.1
    ```

### 3. Run Project:

- When running the above command, it will automatically run the project.

    ```bash
        make start & make logs
    ```
### 4. Results:

- Web Server http://localhost:9090
- Mail Server http://localhost:8009
- Mysql connection:  
    ```bash
        - Server Host: localhost
        - Port: 3308
        - Database: laravel8
        - Username: root
        - Password: secret
    ```
- Go to Web instance: 
    ```bash
        make shell
    ```
- Go to Mysql instance: 
    ```bash
        make mysql
    ```
- Go to Databases: 
    ```bash
        make db
    ```
- Go to Redis instance: 
    ```bash
        make redis
    ```
- Kill container: 
    ```bash
        make stop
    ```
- Destroy container: 
    ```bash
        make destroy
    ```
