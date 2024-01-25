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
do projeto e deve ser declarado nas Models e Controllers.
```php
# Isso é suficiente para deixar o arquivo seguro
declare(strict_types=1)
```

## ROUTE
Redirecionamento no arquivo de rotas (web.php)
```php
Route::get('/', function(){
    return to_route('dashboard');
})

Route::resource('comments', \App\Http\Controllers\CommentController::class)
  ->only(['index', 'store', 'edit', 'update'])
  ->auth(['auth', 'verified'])
```

Redirecionamento no controller
```php
return to_route('comments.index');
```

Visualizando todas as rotas via terminal
```php
php artisan route:list
```

## MIDDLEWARE
Middleware é um filtro aplicado na chamada das rotas.

## CONFIG
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
## VALIDATE
Validação de campos do formulário.
```php
$validated = $request->validate([
    'message' => 'required|string|max:255'
]);
```

Mensagens de validação personalizada
```php
$validated = $request->validate([
    'message' => 'required|string|max:255'
],
[
'message.required' => 'Campo obrigatório',
'message.max' => 'Limite de caracter excedido. O limite é de 255 caracteres'
]);
```

## AUTHENTICATE
#### Get User login
Obter o usuário logado no controller
```php
# opção 1 - import Facade
Auth::user()
# opção 2
auth()->user()
# opção 3
$request->user()
```

## RELACIONAMENTOS
O relacionamento a nível de banco de dados devem ser efetuados no Model
##### HasMany - Tem muitos
```php
# HasMany na Model User. Por padrão como são muitos comentários o nome da função é no plural
public function comments(): HasMany
{
  return $this->hasMany(Comment::class);
}
```
##### BelongsTo - Pertence há um

```php
# BelongsTo na Model Comment. Por padrão como é pertence há um o nome da função é no singular.
public function user(): BelongsTo
{
  return $this->belongsTo(Comment::class);
}
```

## MIGRATION
Incluindo a chave estrageira de user nos comentários. No arquivo de migration de comments.
```php
# Editar esse arquivos e incluir a linha a seguir.
$table->foreignId('user_id')
    ->index()
    ->constrained()
    ->cascadeOnDelete();
```

## Mass Assignment Protection
Trata-se de uma proteção de gravação em massa.

Para permitir a persistência de dados temos que autorizar os campos.
```php
# São campos que quero permitir
protected $fillable = ['user_id', 'message']; # assim permito apenas esses campos

# São os campos que NÃO quero permitir, ou seja, quero preserva-los de alterações.
protected $guarded = ['id']; # Assim permito todos os campos exceto o id
```

## Terminal Laraval Tinker
É possível interagir com o projeto através do terminal.
```php
php artisan tinker

# dentro do terminal
$teste = Comment::all()

# se a model não for encontrar
$teste = \App\Models\Comment::all()
```

## VIEW
Passando dados para view.

CONTROLLER
```php
return view('comments.index', [
    'comments' => Comment::with('user')->latest()->get();
]);
```

Exibindo os dados
```php
# view/comments/index.blade.php
<div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
    @foreach($comments as $comment)
        <div class="p-6 flex space-x-2">
            <div class="flex-1">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-gray-800">{{$comment->user->name}}</span>
                        <small class="ml-2 text-sm text-gray-600" >{{$comment->created_at->format('d/m/Y H:i')}}</small>
                    </div>
                </div>
                <p>{{$comment->message}}</p>
            </div>
        </div>
    @endforeach
</div>
```
## CONTROLLER
No Controller é possível verificar se um usuário tem permissão para efetuar uma alteração utilizando o método reservado
do authorize().

```php
#verifica se o usuário logado tem permissão para alterar
$this->authorize('update', $comment);
```

#### AUTHORIZE e POLICIES
O método authorize() bloqueia qualquer tentativa de acesso. Para criar as permissões é necessário criar as politica de
de permissão ou as Polices. Importante para aumentar as segurança das aplicações.

```php
# make:policy <Nome_da_Policy> --model=<Nome_do_Model>
php artisan make:policy CommentPolicy --model=Comment

# Configurando a policy
public function update(User $user, Comment $comment)
{
    # retorna um booleano.
    return $comment->user()->is($user);
}
```
