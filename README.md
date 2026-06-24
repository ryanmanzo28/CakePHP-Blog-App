# Hydracor Blog App
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [How to use](#how-to-use)

## Overview
This is a HydraCor company branded blog web app, built in cakephp and running containerized on a docker contained environment. it allows users to creat and publish articles, follow other users, and view a feed of articles from followed users. Admin users can manage users,set filters, and set announcements that are displayed on the dashboard. The app is designed to be simple and easy to use, with a clean and modern interface.

## Prerequisites
- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

## How to use
### Installation
1. Clone this repository.
```
$ git clone https://github.com/ryanmanzo28/CakePHP-Blog-App.git
```
2. `cp .env.example .env` and replace values with your own
3. `docker-compose up -d`
4. `docker-compose exec app /bin/bash -c "composer i"`
