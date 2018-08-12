# Examples of use

## Making requests
### With the Request object
The request object returns itself for most calls so calls can be chained together.

#### Getting a web page
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;

$webPageHTML = Request::new()
    ->get('http://www.google.com')
    ->send()
    ->getBody();
``` 

#### Posting raw data
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;

$data = 'foo';

$responseBody = Request::new()
    ->post('https://www.example.com')
    ->withContentType('text/plain')
    ->withData($data)
    ->send()
    ->getBody();
```

#### Posting form data
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;

$data = ['fieldName' => 'fieldValue'];

$responseBody = Request::new()
    ->post('https://www.example.com')
    ->withData($data)
    ->send()
    ->getBody();
```

#### Posting files
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;

$responseBody = Request::new()
    ->post('https://www.example.com')
    ->withFile('/path/to/file')
    ->send()
    ->getBody();
```

#### Downloading files
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;

$responseBody = Request::new()
    ->get('https://www.example.com/file.zip')
    ->downloadToFile('/some/file.zip')
    ->send()
    ->getBody();
```

#### Setting headers
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;

$responseBody = Request::new()
    ->get('https://www.example.com')
    ->withHeader('Authorization', $someToken)
    ->withHeader('Somthing-important', 'Not really')
    ->send()
    ->getBody();
```

#### Checking the status
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;
use mbruton\Transport\HTTP\Message\Response;

$response = Request::new()
    ->post('https://www.example.com')
    ->withFile('/path/to/file')
    ->send();
    
if ($reponse->getStatusCode() == 200) {
    print $response->getBody();
}
```

#### Or getting the headers
```php
namespace MyApp;
use mbruton\Transport\HTTP\Message\Request;
use mbruton\Transport\HTTP\Message\Response;

$response = Request::new()
    ->post('https://www.example.com')
    ->withFile('/path/to/file')
    ->send();

$headers = $response->getHeaders(); 
```

### With the Client object
The client object is used by the Response object to make the requests and process the response.  You can use the Client directly if you require
such things as access to the cookies.

#### Get
```php
namespace MyApp;
use mbruton\Transport\HTTP\Client;
use mbruton\Transport\HTTP\Message\Response;

$headers = [];
$httpClient = new Client();
$response = $httpClient->request('http://www.example.com', Client::REQUEST_GET, $headers);
```

#### Post
```php
namespace MyApp;
use mbruton\Transport\HTTP\Client;
use mbruton\Transport\HTTP\Message\Response;

$headers = [];
$httpClient = new Client();
$response = $httpClient->request('http://www.example.com', Client::REQUEST_POST, $headers, 'data');
```

#### Getting cookies
```php
namespace MyApp;
use mbruton\Transport\HTTP\Client;
use mbruton\Transport\HTTP\Message\Response;

$headers = [];
$url = 'http://www.example.com';
$httpClient = new Client();
$response = $httpClient->request($url, Client::REQUEST_GET);
$cookies = $httpClient->getCookieJar()->getCookiesForURL($url);

```