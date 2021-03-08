# Filas e Processamento Assíncrono com Laravel

- Algumas vezes nossas aplicações recebem uma demanda muito grande de solicitações e muitas vezes essas solicitações são pesadas, ou seja, tem um tempo de processamento demorado e o tempo de resposta também é demorado, como o envio de um e-mail. Se a aplicação precisa processar algo pesado com um tempo de resposta grande o ideal é liberar o usuário, evitando o travamento da aplicação e executando essa tarefa enquanto o servidor estiver ocioso. Além de melhorar a usabilidade isso ajuda a trabalhar de forma mais inteligente, sem sobrecarregar com múltiplos processos simultâneos. Mas como podemos fazer tudo isso? Este projeto te mostrará como através do uso de filas.


## Passo 1: Configurando o projeto

- O primeiro passo é configurarmos nosso arquivo ```.env``` para que possamos enviar nossos e-mails. Para isso crie uma conta em <a>https://mailtrap.io</a>, acesse seu inbox e em SMTP Settings selecione Laravel e o site te mostrará o que substituir no seu arquivo ```.env```.

## Passo 2: Criando uma rota para enviar um email

- Agora iremos criar a rota responsável por enviar os e-mails e também a EmailController.

- Primeiro crie a controller com o comando:

```
php artisan make:controller EmailController
```
- Agora no arquivo ```routes/web.php``` adicione a seguinte linha:

```
Route::get('email', 'EmailController@sendEmail');
```

- Precisamos também criar uma classe mailable. Cada tipo de e-mail enviado pelo Laravel é representado como uma classe "mailable". Para criar basta executar este comando no seu terminal:

```
php artisan make:mail SendMailable
```

- Isso irá criar um arquivo ```App\Mailable\SendMailable.php```.

- Dentro da nossa ```EmailController``` iremos implementar a função ```sendMail()```:

```
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailable;

public class EmailController extends Controller
{
    public function sendEmail() 
    {
        Mail::to('mail@appdividend.com')->send(new SendMailable());
        echo 'email sent';
    }
}
```

- O arquivo ```SendMailable.php``` por hora não será modificado.

- Agora execute o servidor do Laravel com ```php artisan serve``` e navegue até ``` http://localhost:8000/email```, você notará que existe um delay até a página ser carregada e o e-mail ser enviado, e é por isso que utilizaremos filas.


## Passo 3: Configurando a fila

- Nós podemos trabalhar com filas usando Database, RedIs e outros drivers mencionados em <a>https://laravel.com/docs/8.x/queues#driver-prerequisites</a>. Neste exemplo iremos utilizar o Database, para configurá-lo vá até seu arquivo ```.env``` e substitua o parâmetro ```QUEUE_CONNECTION=sync``` por ```QUEUE_CONNECTION=database```.

- Agora precisamos criar as tabelas que serão utilizadas. Por padrão o Laravel já contém estas tabelas, basta rodar os seguintes comandos:

```
php artisan queue:table

php artisan migrate
```

- Isto irá criar no seu banco de dados duas tabelas: ```jobs``` e ```failed_jobs```.


## Passo 4: Criando um job

- Agora precisamos criar um job cujo papel será enviar os e-mails. Para criar um job execute o seguinte comando:

```
php artisan make:job SendMailJob
```

- Isto irá criar o arquivo ```App\Jobs\SendMailJob.php```, dentro dele iremos colocar a função sendMail, seu arquivo deverá ficar assim:

```
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to('mail@appdividend.com')->send(new SendMailable());
    }
}
```

- Na nossa controller precisaremos disparar esse job, podemos fazer isso através do ```dispatch```. Sua controller deverá ficar assim:

```
class EmailController extends Controller
{
    public function sendEmail()
    {
        dispatch(new SendEmailJob());

        echo 'email sent';
    }
}
```

- Se você rodar o servidor e tentar disparar o e-mail como anteriormente notará que ainda existe um delay, isto porque pegamos a lógica da nossa controller e apenas implementamos em outro lugar, nós precisamos enviar o e-mail por de baixo dos panos, colocando um delay na nossa fila.

```
public function sendEmail()
{
    $emailJob = (new SendMailJob())->delay(Carbon::now()->addSeconds(1));
    dispatch($emailJob);
    echo 'email sent';
}
```

- Agora se você fizer uma request para nossa rota notará que não há delay algum na resposta, mas também notará que nenhum e-mail foi enviado. Como podemos fazer com que os e-mails sejam enviados?

- Vá até seu database e dê uma olhada na tabela Jobs, notará que há uma inserção lá, isso significa que o processo do job ainda não foi iniciado. Existe algo no Laravel chamado de ```Queue Worker```. Isto nada mais é do que um comando do Laravel que irá executar novos jobs que sejam colocados na nossa fila. Você pode usá-lo através do seguinte comando:

```
php artisan queue:work
```

- Quando rodar este comando você verá algo parecido com isto no seu terminal:

```
[2021-03-08 13:57:14][1] Processing: App\Jobs\SendMailJob
[2021-03-08 13:57:17][1] Processed:  App\Jobs\SendMailJob
```

- Se verificar seu inbox notará que agora sim os e-mails foram enviados. Sempre que uma request for feita e um novo processo entrar na fila ele será executado pelo Queue Worker.
