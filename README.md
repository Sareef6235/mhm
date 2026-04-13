# Monthly Plan Management System (PHP + MySQL + Vanilla JS)

## Run
1. Create DB and import `schema.sql`.
2. Configure DB credentials through env vars (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) or edit `config.php`.
3. Serve with PHP:

```bash
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000/index.php`.

## API
- `GET api.php?api=list&month=&search=`
- `GET api.php?api=ustads`
- `GET api.php?api=subjects`
- `POST api.php?api=save` (insert/update + inline subject update)
- `POST api.php?api=delete`
- `POST api.php?api=reorder`

All API responses are JSON.
