# gym-core-ms
An admin microservice for operating an unstaffed gym 

## Stack
- PHP + Laravel
- Vue (Vite)
- Postgres
- pgAdmin
- Docker Compose

## Quick start (Windows / PowerShell)
Copy docker env example:

```powershell
Copy-Item .\.env.docker.example .\.env.docker
```

Start containers:

```powershell
docker compose --env-file .\.env.docker up -d --build
```

Create Laravel project (first run):

```powershell
New-Item -ItemType Directory -Force .\src | Out-Null
docker compose --env-file .\.env.docker run --rm app sh -lc "composer create-project laravel/laravel ."
```

Configure Laravel env:

```powershell
Copy-Item .\src\.env.example .\src\.env
docker compose --env-file .\.env.docker run --rm app sh -lc "php artisan key:generate"
```

Then edit `src/.env` DB settings to:

- `DB_CONNECTION=pgsql`
- `DB_HOST=postgres`
- `DB_PORT=5432`
- `DB_DATABASE=gym_core`
- `DB_USERNAME=gym`
- `DB_PASSWORD=gym_pass`

Open:
- App: `http://localhost:8081`
- Vite: `http://localhost:5174`
- pgAdmin: `http://localhost:5051`
