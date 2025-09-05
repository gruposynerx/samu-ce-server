![Samu 360](https://preview.dragon-code.pro/Multintegrada/Samu-360-server.svg?brand=laravel&season=disabled&mode=auto)

<p align="center">🚑 A software solution that allows control of all SAMU processes 🩺</p>

<p align="center">
  <a href="#%EF%B8%8F-about-the-project">About the project</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-technologies">Technologies</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-getting-started">Project Installation</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-how-to-contribute">How to contribute</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
  <a href="#-license">License</a>
</p>

##  👨‍⚕️ About the project
With this API, it is possible to integrate internal SAMU procedures, improving assistance, practicality and process efficiency.

## 🚀 Technologies
<img src="https://img.shields.io/badge/laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=fff&labelColor=FF2D20" /> <img src="https://img.shields.io/badge/postgres-0064a5?style=for-the-badge&logo=postgresql&logoColor=fff&labelColor=0064a5" />

## 📖 External APIs
- [CADSUS](https://cns-api.azurewebsites.net/api)

## 💻 Project Installation

### Requirements

- [Docker](https://www.docker.com/)

### Installation Steps

Clone the repository and navigate to project directory
```sh
git clone https://github.com/multintegradabr/samu360-server.git
cd samu360-server
```

Create enviroment based on example

```sh
cp .env.example .env
```

Install the application's dependencies
```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```
> _This command uses a small Docker container containing PHP and Composer to install the application's dependencies_

Start the Sail container

```sh
./vendor/bin/sail up -d --build
./vendor/bin/sail npm install //git hooks
```

Cool! Now the project is running on `localhost:80`

Check [configuring a shell alians](https://laravel.com/docs/10.x/sail#configuring-a-shell-alias) to run commands without sail path prefix

## 📝 Commands

```php
app:fill-base-types
app:migrate-diagnostic-hypotheses
app:migrate-data-scene-register-antecedents-table
app:import-bases-location
app:fill-first-user-status-history
```

## 🤔 How to contribute

**Follow the steps below**

```bash
# Clone the repo and navigate to project directory
$ git clone https://github.com/multintegradabr/samu360-server.git && cd samu360-server

# Create a branch with your feature preferably based on the **DEV** branch
$ git checkout -b my-feature

# Make the commit with your changes
$ git commit -m 'feat: My new feature'

# Send the code to remote branch
$ git push origin my-feature

# Open the pull Request and wait for approval 😄
```

After your pull request is merged, you can delete your branch

---

Made with <img src="https://raw.githubusercontent.com/Tarikul-Islam-Anik/Microsoft-Teams-Animated-Emojis/master/Emojis/Smilies/Blue%20Heart.png" alt="PO" width="20" height="20" /> by Multintegrada Tecnologia 👋 [Know us](https://multintegrada.com.br/)
