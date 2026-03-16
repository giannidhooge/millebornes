# Mille Bornes

[![Tests](https://github.com/giannidhooge/millebornes/actions/workflows/tests.yml/badge.svg)](https://github.com/your-username/millebornes/actions/workflows/tests.yml)
[![Lint](https://github.com/giannidhooge/millebornes/actions/workflows/lint.yml/badge.svg)](https://github.com/your-username/millebornes/actions/workflows/lint.yml)

This project is a web-based implementation of the classic French card game, Mille Bornes.

## Key Features

*   Multiplayer gameplay with real-time updates.
*   Authentic game rules, including hazards, remedies, and safeties.
*   Lobby system for players to join and start games.
*   Interactive UI built with Vue.js.

## Technologies Used

*   **Backend:** Laravel, PHP
*   **Frontend:** Vue.js, TypeScript, Inertia.js
*   **Database:** MySQL
*   **Real-time:** Pusher

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/millebornes.git
    cd millebornes
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Set up your environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Update your `.env` file with your database credentials and other settings.*

4.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

5.  **Build frontend assets:**
    ```bash
    npm run dev
    ```

6.  **Start the development server:**
    ```bash
    php artisan serve
    ```
    *You will also need to run the Reverb server for real-time features:*
    ```bash
    php artisan reverb:start
    ```

## Running Tests

To run the PHPUnit tests, use the following command:

```bash
php artisan test
```

