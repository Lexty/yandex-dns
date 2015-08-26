# yandex-dns

PHP library for working with [Yandex DNS API](https://tech.yandex.ru/pdd/doc/concepts/api-dns-docpage/).

## Example usages

```php
<?php
use Lexty\YandexDns\Dns;

$dns = new Dns('example.org', 'your_pdd_token');

// Getting all records
$records = $dns->records();

// Add new record
$response = $dns->add(Dns::TYPE_CNAME, [DNS::FIELD_SUBDOMAIN => 'subdomain', Dns::FIELD_CONTENT => 'example.org']);

// Change record
$data = $dns->edit($response['record'][Dns::FIELD_RECORD_ID], [Dns::FIELD_SUBDOMAIN => 'sub', Dns::FIELD_CONTENT => 'example.org']);

// Delete record
$data = $dns->remove($response['record'][Dns::FIELD_RECORD_ID]);