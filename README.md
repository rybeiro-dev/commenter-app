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
```shell
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

Incluir Mensagens de validação personalizada

```php
$validated = $request->validate([
    'message' => 'required|string|max:255'
],
[
'message.required' => 'Campo obrigatório',
'message.max' => 'Limite de caracter excedido. O limite é de 255 caracteres'
]);
```

Importante sempre manter o seu código limpo e com Principio de responsabilidade única.
Vou refator o código para manter a validação isolada.

```shell
php artisan make:request CommentValidationRequest
```

A classe foi criada no diretório Http/Requests. Vamos efetuar a configuração.

```php
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => 'required|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Campo obrigatório',
            'message.max' => 'O tamanho máximo é de 255 caracteres'
        ];
    }
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
# HasMany na Model User. Por padrão como são muitos comentários o nome da 
# função é no plural
public function comments(): HasMany
{
  return $this->hasMany(Comment::class);
}
```
##### BelongsTo - Pertence há um

```php
# BelongsTo na Model Comment. Por padrão como é pertence há um o nome da função 
# é no singular.
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

```shell
php artisan tinker
```

```php
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
                        <small class="ml-2 text-sm text-gray-600" >
                          {{$comment->created_at->format('d/m/Y H:i')}}
                        </small>
                    </div>
                </div>
                <p>{{$comment->message}}</p>
            </div>
        </div>
    @endforeach
</div>
```
## CONTROLLER
No Controller é possível verificar se um usuário tem permissão para efetuar uma 
alteração utilizando o método reservado do authorize().

```php
#verifica se o usuário logado tem permissão para alterar
$this->authorize('update', $comment);
```

Importante se estiver utilizando o Principio de resposabilidade única, a chamada 
de validação:

```php
# exemplo de validação no update
public function update(CommentValidationRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return to_route('comments.index');
    }
```

#### AUTHORIZE e POLICIES
O método authorize() bloqueia qualquer tentativa de acesso. Para criar as 
permissões é necessário criar as politica de permissão ou as Polices. Importante 
para aumentar as segurança das aplicações.

```shell
# make:policy <Nome_da_Policy> --model=<Nome_do_Model>
php artisan make:policy CommentPolicy --model=Comment
```

```php
# Configurando a policy
public function update(User $user, Comment $comment)
{
    # retorna um booleano.
    return $comment->user()->is($user);
}
```

## Notifications
Através da criação de um classe de notificação o envio através de email, sms, slack etc.
```shell
# gerar a classe de notificação via artisan
php artisan make:notification NewCommentNotification
```

#### Configurando a classe NewCommentNotification

```php
declare(strict_types=1);

# ... código omitido
use Illuminate\Support\Str;
# ... código omitido

public function __construct(puclic Comment $comment) { }

public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
      ->subject("Novo comentario")
      ->greeting("Novo comentário de {$this->comment->user->name}")
      ->line(Str::limit($this->comment->message, limit:50))
      ->action('Ver comentário', route('comments.index'))
      ->line('Obrigado por usar nossa aplicação');
}

# ... código omitido

```

## Events
Para usar os eventos temos que seguir pelo menos 3 passos:
- Criar o evento
- Criar um listener
- Registrar o listener no evento.

Importante incluir o disparador de eventos no Modelo: ```$dispatchesEvents = [];```

```php
# Criando o evento
php artisan make:event CommentCreatedEvent

# configurando a classe do evento para receber o comentario
public function __construct(public Comment $comment) {}

# e no Model Comment, configurar o dispatches para quando o comentário for criado chame o evento

protected $dispatchesEvents = [ 'created' => CommentCreatedEvent::class ];
```

## Listener
o listener é o observer no Laravel, o listener é o responsável por disparar os 
eventos. O Listener trabalha com filas, deve-se configurar .env

```shell
# criando o listener
php artisan make:listener SendCommentCreatedNotifications --event=CommentCreatedEvent
```

```php
# configurando a classe
declare(type_strict=1);
# ... trecho de código omitido

class SendCommentCreatedNotifications implements ShouldQueue {
    public function handle(CommentCreatedEvent $event): void
    {
        foreach(user::whereNot('id', $event->comment->user_id)->cursor() as $user){
            $user->notify(new NewCommentNotification($event->comment));
        }
    }
}
```

# Registrar o evento
Registrar no listner no evento para ouvir todas as criações de Commentários.

No diretório Provider abrir a classe EventServiceProvider

```php
# adicionar CommentCreatedEvent::class ...

protected $listen = [
    CommentCreatedEvent::class => [
        SendCommentCreatedNotifications::class,
    ],

    # ... trecho de código omitido
];
```

## Mailpit - Teste de envio de email
Caixa de email fake para testar o envio de email, antes de subir em produção. 
Para isso vamos subir o serviço do Mailpit incluíndo configuração no docker-compose.yaml

```php
# configuração 
mailpit:
  image: 'axllent/mailpit:latest'
  container_name: mailpit
  restart: unless-stopped
  ports:
    - '${FORWARD_MAILPIT_PORT:-1025}:1025' 
    - '${FORWARD_MAILPIT_UI_PORT:-8025}:8025'
  networks:
    - sail
```

## QUEUE
Podemos utilizar o Redis como fila para essa aplicação, configurar no 
arquivo .env a variável QUEUE_CONNECTION=redis mas para processar a fila é 
necessário executar o seguinte comando.

```shell
php artisan queue:work
```

# DICAS
laravel/debugbar é para tirar metrica de execução de processos e só funciona em 
modo debug=true


# IMPORTANTE:
O método _cursor()_ utilizado em uma _query_ do Laravel evita de carregar todos
 os registros na mémoria de uma vez para não sobrecarregar a aplicação.