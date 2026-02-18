# Recommended Project Structure

```text
bangsa/
  apps/
    api/
      src/
      prisma/
    web/
      app/
      components/
  packages/
    ui/
    shared-types/
    config/
  infra/
    docker/
    terraform/
  docs/
```

Current implementation in this repository is backend-first and includes:

```text
src/
  config/
  lib/
  modules/
    auth/
    billing/
    groups/
    members/
    dashboard/
    platform/
  types/
prisma/
  schema.prisma
  seed.ts
docs/
```