# Commenter App
## Criando o projeto passo a passo
Nesse projeto vou utilizar mysql e redis.
### Criando o projeto
```shell
curl -s "https://laravel.build/example-app?with=mysql,redis" | bash
```

### Incluindo a camada de autenticação
```shell
composer require laravel/breeze --dev

php artisan breeze:install blade
```


## TIPAGEM
Uma camada importe de segurança é trabalhar com tipagem. É recomendado o uso de tipagem forte para aumentar a segurança                                                                                                                                                     
dos projeto.
```php
# Isso é suficiente para deixar o arquivo seguro
declare(strict_types=1)
```
Lembrando que essa chamada deve ser feita em cada arquivo PHP do seu projeto Model e Controller.

### ROUTE
Redirecionamento no arquivo de rotas (web.php)
```php
Route::get('/', function(){
    return to_route('dashboard');
})
```

Redirecionamento no controller
```php
return to_route('comments.index');
```

Visualizando todas as rotas via terminal
```php
php artisan route:list
```

### MIDDLEWARE
Middleware é um filtro aplicado na chamada das rotas.

### CONFIG
Para criar configurações personalizadas e utilizar em qualquer lugar do projeto
```php
# criar o arquivo no diretorio root/config
# commenter.php
return [
'app_name' => env('APP_NAME')
];

# efetuando a chamada
config('commenter.app_name');
```
#### VALIDATE
```php
$validated = $request->validate([
    'message' => 'required|string|max:255'
]);
```

### Get User login
Obter o usuário logado no controller
```php
# opção 1 - import Facade
Auth::user()
# opção 2
auth()->user()
# opção 3
$request->user()
```
