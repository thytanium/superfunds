# Superfunds

This exercise requires building a data model and back-end service to support a couple of the basic uses cases for a web application used to curate a database that serves as single source of truth for managing investment funds.

Fund Records can be created / read / updated / deleted manually in this application. There also will be back-end processes that are creating new records automatically. Some of the automatically created records will be duplicates that will need to be manually reconciled.

### Chosen stack

-   Laravel
-   PostgreSQL

## Running

### Requirements

-   PHP >= 8.1
-   Composer
-   Docker

### Installation steps

1. Download the source code

    ```sh
    git clone https://github.com/thytanium/superfunds
    ```

2. Install Composer dependencies

    ```sh
    composer install
    ```

3. Copy `.env` file

    ```sh
    cp .env.example .env
    ```

4. Start Docker containers

    ```sh
    ./vendor/bin/sail up -d
    ```

5. Run database migrations and default data seeder
    ```sh
    ./vendor/bin/sail artisan migrate --seed
    ```

## Tasks

-   [x] Design and create a data model to store data for the entities described above. Please document your ER diagram.

-   [x] Create a back-end service to support the following use cases:

    -   [x] Display a list of funds optionally filtered by Name, Fund Manager, Year
    -   [x] An Update method to update a Fund and all related attributes.

-   [x] Create an event-driven back end process to support:

    -   [x] If a new fund is created with a name and manager that matches the name or an alias of an existing fund with the same manager, throw a duplicate_fund_warning event.
    -   [x] Write a process to Consume the duplicate_fund_warning event
    -   [x] Bonus if time permitting: Add a method to the service created in #2 that will return a list of potentially duplicate funds

## Data modeling and ER diagram

The first entity to be modeled is _Company_ since one _Manager_ manages many _Fund_ instances and each _Fund_ is invested in one _Company_. Therefore, _Company_ is the deepest lying entity among those relationships.

From there, we add the entity _Manager_, and finally the entity _Fund_. We left _Fund_ to be the last so we can add FKs to the already existing entities _Manager_ and _Company_.

### Database tables

#### managers

| name | type         | extra    | comment |
| ---- | ------------ | -------- | ------- |
| id   | int          | PK       |         |
| name | varchar(255) | not null |         |

#### companies

| name | type         | extra    | comment |
| ---- | ------------ | -------- | ------- |
| id   | int          | PK       |         |
| name | varchar(255) | not null |         |

#### funds

| name       | type         | extra    | comment                                      |
| ---------- | ------------ | -------- | -------------------------------------------- |
| id         | int          | PK       |                                              |
| name       | varchar(255) | not null |                                              |
| start_year | int          | not null |                                              |
| aliases    | json         |          | Using type json to store as a list of string |
| manager_id | int          | not null | FK to `managers`                             |
| company_id | int          | not null | FK to `companies`                            |

**Note**: The field `aliases` could have also been modeled as a separate table called `aliases` and creating a one-to-one relationship between `aliases` and `funds`, which will represent the alias corresponding to each fund.
The decision to use the json column instead is purely economical, avoiding creating an additional table for now.

#### potential_duplicate_funds

This table stores all potential duplicate fund warnings.

| name                   | type         | extra    | comment                                                                          |
| ---------------------- | ------------ | -------- | -------------------------------------------------------------------------------- |
| id                     | int          | PK       |                                                                                  |
| offending_fund_name    | varchar(255) | not null | The offending fund name.                                                         |
| offending_manager_name | varchar(255) | not null | The offending fund manager name.                                                 |
| offending_fund_id      | int          | not null | The offending fund ID. FK to `funds`.                                            |
| related_fund_id        | int          | not null | The related fund ID (the one whose name or alias already exists). FK to `funds`. |

### ER Diagram

![ER Diagram](/er_diagram.png)

## API Service

-   The API service rests behind the path `/api`.
-   Sending the request header `Accept: application/json` is suggested but not required.
-   For lists, the query parameter `page` indicates which page needs to be retrieved from the paginated result.
-   Accepted content types for POST/PUT requests:
    -   `form-data`
    -   `application/x-www-form-urlencoded`
    -   `application/json`

### Implementation considerations

-   In an attempt to keep controller logic and data handling separate, we perform all data operations related to Funds in the class `FundRepository`.
-   For other models, the same `*Repository` class approach might not be used in an attempt to save time on this assignment.

### Resources

#### Funds

-   `GET /funds`: List all available funds
    -   Query parameters:
        -   `page`: integer. Indicates the requested page number.
        -   `name`: sring. Filter by fund name.
        -   `start_year`: integer. Filter by start year.
        -   `manager_name`: string. Filter by manager name.
        -   `manager_id`: integer. Filter by manager ID.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/funds?manager_id=1&page=2' \
        --header 'Accept: application/json'
        ```
-   `POST /funds`: Create a new fund. This endpoint could fire the event `DuplicateFundWarning` and be consumed by the `StoreDuplicateFundWarning`.
    -   Body:
        -   `name`: string, required. Name for the fund.
        -   `start_year`: integer, required. Start year for the fund.
        -   `aliases`: string (json). Aliases for this fund.
        -   `manager_id`: integer, required. Database ID of the manager of this fund.
        -   `company_id`: integer, required. Database ID of the company of this fund.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/funds' \
        --header 'Accept: application/json' \
        --header 'Content-Type: application/x-www-form-urlencoded' \
        --data-urlencode 'name=accusantium' \
        --data-urlencode 'start_year=2023' \
        --data-urlencode 'manager_id=1' \
        --data-urlencode 'company_id=1'
        ```
-   `PUT /funds/:id`: Update a fund
    -   Body:
        -   `name`: string. Name for the fund.
        -   `start_year`: integer. Start year for the fund.
        -   `aliases`: string (json). Aliases for this fund. **Note**: the value provided will not be appended, it will replace the current list of aliases.
        -   `manager_id`: integer. Database ID of the manager of this fund.
        -   `company_id`: integer. Database ID of the company of this fund.
    -   **Example:**
        ```sh
        curl --location --request PUT 'http://localhost/api/funds/1' \
        --header 'Accept: application/json' \
        --header 'Content-Type: application/x-www-form-urlencoded' \
        --data-urlencode 'start_year=2018' \
        --data-urlencode 'manager_id=2'
        ```
-   `DELETE /funds/:id`: Delete a fund
    -   **Example:**
        ```sh
        curl --location --request DELETE 'http://localhost/api/funds/1'
        ```

#### Potential duplicate funds

-   `GET /potential_duplicate_funds`: List all potential duplicate funds
    -   Query parameters:
        -   `page`: integer. Indicates the requested page number.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/potential_duplicate_funds'
        ```

#### Companies

-   `GET /companies`: List all available companies
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/companies'
        ```
-   `POST /companies`: Create a new company
    -   Body:
        -   `name`: string, required. Name for the company.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/companies' \
        --header 'Accept: application/json' \
        --header 'Content-Type: application/x-www-form-urlencoded' \
        --data-urlencode 'name=Canoe'
        ```
-   `GET /companies/:id`: Show a specific company.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/companies/1'
        ```
-   `PUT /companies/:id`: Update a fund
    -   Body:
        -   `name`: string. Name for the company.
    -   **Example:**
        ```sh
        curl --location --request PUT 'http://localhost/api/companies/1' \
        --header 'Accept: application/json' \
        --header 'Content-Type: application/x-www-form-urlencoded' \
        --data-urlencode 'name=New company name'
        ```
-   `DELETE /companies/:id`: Delete a company.
    -   **Example:**
        ```sh
        curl --location --request DELETE 'http://localhost/api/companies/1'
        ```

#### Managers

-   `GET /managers`: List all available managers
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/managers'
        ```
-   `POST /managers`: Create a new manager
    -   Body:
        -   `name`: string, required. Name for the manager.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/managers' \
        --header 'Accept: application/json' \
        --header 'Content-Type: application/x-www-form-urlencoded' \
        --data-urlencode 'name=Canoe'
        ```
-   `GET /managers/:id`: Show a specific manager.
    -   **Example:**
        ```sh
        curl --location 'http://localhost/api/managers/1'
        ```
-   `PUT /managers/:id`: Update a fund
    -   Body:
        -   `name`: string. Name for the manager.
    -   **Example:**
        ```sh
        curl --location --request PUT 'http://localhost/api/managers/1' \
        --header 'Accept: application/json' \
        --header 'Content-Type: application/x-www-form-urlencoded' \
        --data-urlencode 'name=New manager name'
        ```
-   `DELETE /managers/:id`: Delete a manager.
    -   **Example:**
        ```sh
        curl --location --request DELETE 'http://localhost/api/managers/1'
        ```

## Unit tests

Due to the limited time to write this solution, testing was limited to one
particular function: detecting potential duplicates when creating a new fund.

In that test, we create an initial fund, then attempt to create another one
with the same name, and finally look for new records in the database table where
potential duplicates are kept.

### Running unit test(s)

```sh
./vendor/bin/sail artisan test
```

## Scalability considerations

### How does the application work as the data set grows increasingly larger?

-   If usage of the application reveals that the list of funds is constantly being filtered by start year,
    then we should consider adding an index to the `funds` table on the `start_year` column.
    Without an index, the presence of many records will make this query run slowly.
    The same could apply to fields like `name` on the table `managers`.
-   When looking for potential fund duplicates, we do a lookup on all `funds` records.
    This query will become slow when the dataset becomes larger.
    The same approach of adding an index will help in this situation.

### How does the application work as the # of concurrent users grows increasingly larger?

-   Many concurrent requests to create a new fund will create a bottleneck in the server
    because the process to detect potential duplicates runs synchronously.
    This process should be queued. Using a queue will also improve user experience
    as users should not have to wait for the detection process to run, as it is irrelevant
    to the output of the create operation.
-   Caching (for example, using a Redis server) will help the server close connections faster
    on requests for lists of records (like list of funds) and therefore increase throughput.

## Project structure

-   `app`
    -   `Events`
        -   `DuplicateFundWarning.php`
    -   `Http`
        -   `Controllers`
            -   `CompanyController.php`
            -   `FundController.php`
            -   `ManagerController.php`
            -   `PotentialDuplicateFundController.php`
        -   `Filters`
            -   `AbstractFilter.php`
            -   `FundFilter.php`
    -   `Listeners`
        -   `StoreDuplicateFundWarning.php`
    -   `Models`
        -   `Company.php`
        -   `Fund.php`
        -   `Manager.php`
        -   `PotentialDuplicateFund.php`
    -   `Repository`
        -   `FundRepository.php`
    -   `Traits`
        -   `Filterable.php`
-   `tests`
    -   `Feature`
        -   `FundTest.php`

The rest of files are the files that come with a Laravel installation.

## If there were more time, I would have...

-   Reduced similar code by creating a single entity to index, create, update, show and delete API records.
    An approach similar to front controller.
-   Modified Laravel's default error handler to standardize erroneous responses a bit more.
-   Looked for a free PHP PaaS to host this project and provide a live URL.
-   Added more tests.
