# FAVI Partner Order API

Simple REST API for receiving and storing partner orders.

## Requirements

- PHP 8.2+
- Composer
- Docker

## Setup

```bash
composer install
docker compose up -d

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
```

## Running Tests

```bash
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:migrations:migrate --env=test --no-interaction
php bin/phpunit
```

## API

### Create Order

`POST /api/orders`

Request:

```json
{
  "partner_id": "nabytek-24",
  "order_id": "OBJ-20260612-8457",
  "expected_delivery_date": "2026-06-24",
  "total_value": "8450.00",
  "products": [
    {
      "product_id": "K-1187-DB",
      "name": "Dubova jidelni zidle",
      "price": "1890.00",
      "quantity": 4
    },
    {
      "product_id": "L-2043-BK",
      "name": "Stolni lampa cerna",
      "price": "890.00",
      "quantity": 1
    }
  ]
}
```

Success response:

```json
{
  "status": "success",
  "id": 1
}
```

Possible responses:

- `201 Created`
- `409 Conflict` when the same `partner_id` and `order_id` already exists
- `422 Unprocessable Entity` when validation fails

### Update Expected Delivery Date

`PATCH /api/orders/{partnerId}/{orderId}/delivery-date`

Request:

```json
{
  "expected_delivery_date": "2026-06-28"
}
```

Success response:

```json
{
  "status": "success",
  "partner_id": "nabytek-24",
  "order_id": "OBJ-20260612-8457",
  "expected_delivery_date": "2026-06-28"
}
```

Possible responses:

- `200 OK`
- `400 Bad Request` when request JSON or date is invalid
- `404 Not Found` when order was not found

## Implementation Notes

- Order is identified by `partner_id` and `order_id`, because partner order IDs are not globally unique.
- There is a database unique constraint for `partner_id` and `order_id`.
- Money values are stored as Doctrine `DECIMAL` and handled as decimal strings in request DTOs.
- The original request body is stored in `rawPayload`.
- Authentication is not implemented, as requested in the assignment.

## Tests

Integration tests cover the main order API flow:

- valid order is stored in the database;
- order must contain at least one product;
- duplicate `partner_id` and `order_id` returns `409 Conflict`;
- expected delivery date can be updated.

## Time Spent

Time spent: approximately 7.5 hours.
