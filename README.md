# PFinances - Premium Assets & Portfolio Manager

PFinances is a powerful, self-hosted personal finance application built on the Laravel framework. It provides a comprehensive suite of tools to help you manage your assets, track your portfolio, and gain insights into your financial health.

## Features

- **Dashboard:** A comprehensive overview of your financial status, including account balances, recent transactions, and spending by category.
- **Transactions:** A detailed list of all your transactions, with the ability to filter, sort, and categorize them.
- **Accounts:** Manage all your bank accounts in one place, with support for multiple currencies.
- **Budgets:** Set monthly budgets for different categories and track your spending against them.
- **Categories:** Create custom categories for your income and expenses to better understand your spending habits.
- **Projects:** Track your income and expenses for specific projects.
- **Portfolio:** A high-level overview of your assets and liabilities, with a summary of your net worth.
- **Import:** Import your transactions from a CSV file, with support for both Frollo and Excel formats.
- **Data Integrity Check:** A built-in tool to help you identify and fix inconsistencies in your data.

## How it works

The application is built on the Laravel framework and uses a SQLite database to store your financial data. The frontend is built with Bootstrap, and it uses Alpine.js for some of the interactive components.

### Data Integrity

The application includes two Artisan commands to help you maintain the integrity of your data:

- `app:data-integrity-check`: This command checks for transactions without an account and for accounts with mismatched balances.
- `app:fix-account-balances`: This command recalculates the balance of all accounts based on their transactions.

### Import

The application supports importing transactions from a CSV file. You can choose between two import services:

- **Frollo Import Service:** This service is designed to import CSV files exported from Frollo.
- **Excel Import Service:** This service is a more generic CSV importer that can be used with a variety of file formats.

## Getting Started

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/buda-loco/pfinances.git
    ```

2.  **Install dependencies:**

    ```bash
    composer install
    npm install
    ```

3.  **Create your environment file:**

    ```bash
    cp .env.example .env
    ```

4.  **Generate your application key:**

    ```bash
    php artisan key:generate
    ```

5.  **Create the database file:**

    ```bash
    touch database/database.sqlite
    ```

6.  **Run the migrations:**

    ```bash
    php artisan migrate
    ```

7.  **Build the assets:**

    ```bash
    npm run build
    ```

8.  **Start the development server:**

    ```bash
    php artisan serve
    ```

You can now access the application at `http://localhost:8000`.

## Contributing

Thank you for considering contributing to PFinances! Please feel free to open an issue or submit a pull request.

## License

PFinances is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).