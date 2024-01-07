# Documentação e Ajuda


## Roles e Permissions com Spatie Permissions

### Programaticamente (código)

#### Adicionando uma permissão específica à um usuário
- https://spatie.be/docs/laravel-permission/v5/basic-usage/role-permissions#assigning-roles

```php
$user->givePermissionTo('edit articles');
```

#### Adicionando uma role à um usuário

```php
$user->assignRole('writer');
```

#### Removendo uma role de um usuário

```php
$user->removeRole('writer');
```

- Todos os papéis atuais serão removidor e substituido pelos informados no array

```php
$user->syncRoles(['writer', 'admin']);
```
