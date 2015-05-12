<?php

namespace http 
{

	class Client implements \SplSubject, \Countable 
	{
		private $observers;
		protected $options;
		protected $history;
		public $recordHistory;

		public function __construct($driver = NULL, $persistent_handle_id = NULL) {
		}

		public function reset() {
		}

		public function enqueue(\http\Client\Request $request, $callable = NULL) {
		}

		public function dequeue(\http\Client\Request $request) {
		}

		public function requeue(\http\Client\Request $request, $callable = NULL) {
		}

		public function count() {
		}

		public function send() {
		}

		public function once() {
		}

		public function wait($timeout = NULL) {
		}

		public function getResponse(\http\Client\Request $request = NULL) {
		}

		public function getHistory() {
		}

		public function configure(array $settings) {
		}

		public function enablePipelining($enable = NULL) {
		}

		public function enableEvents($enable = NULL) {
		}

		public function notify(\http\Client\Request $request = NULL) {
		}

		public function attach(\SplObserver $observer) {
		}

		public function detach(\SplObserver $observer) {
		}

		public function getObservers() {
		}

		public function getProgressInfo(\http\Client\Request $request) {
		}

		public function getTransferInfo(\http\Client\Request $request) {
		}

		public function setOptions(array $options = NULL) {
		}

		public function getOptions() {
		}

		public function setSslOptions(array $ssl_option = NULL) {
		}

		public function addSslOptions(array $ssl_options = NULL) {
		}

		public function getSslOptions() {
		}

		public function setCookies(array $cookies = NULL) {
		}

		public function addCookies(array $cookies = NULL) {
		}

		public function getCookies() {
		}

		public static function getAvailableDrivers() {
		}

		public function getAvailableOptions() {
		}

		public function getAvailableConfiguration() {
		}

	}

	interface Exception 
	{
	}

	class Cookie 
	{
		const PARSE_RAW = 1;
		const SECURE = 16;
		const HTTPONLY = 32;

		public function __construct($cookie_string = NULL, $parser_flags = NULL, $allowed_extras = NULL) {
		}

		public function getCookies() {
		}

		public function setCookies($cookies = NULL) {
		}

		public function addCookies($cookies) {
		}

		public function getCookie($name) {
		}

		public function setCookie($cookie_name, $cookie_value = NULL) {
		}

		public function addCookie($cookie_name, $cookie_value) {
		}

		public function getExtras() {
		}

		public function setExtras($extras = NULL) {
		}

		public function addExtras($extras) {
		}

		public function getExtra($name) {
		}

		public function setExtra($extra_name, $extra_value = NULL) {
		}

		public function addExtra($extra_name, $extra_value) {
		}

		public function getDomain() {
		}

		public function setDomain($value = NULL) {
		}

		public function getPath() {
		}

		public function setPath($value = NULL) {
		}

		public function getExpires() {
		}

		public function setExpires($value = NULL) {
		}

		public function getMaxAge() {
		}

		public function setMaxAge($value = NULL) {
		}

		public function getFlags() {
		}

		public function setFlags($value = NULL) {
		}

		public function toArray() {
		}

		public function toString() {
		}

		public function __toString() {
		}

	}

	class Env 
	{
		public static function getRequestHeader($header_name = NULL) {
		}

		public static function getRequestBody($body_class_name = NULL) {
		}

		public static function getResponseStatusForCode($code) {
		}

		public static function getResponseStatusForAllCodes() {
		}

		public static function getResponseHeader($header_name = NULL) {
		}

		public static function getResponseCode() {
		}

		public static function setResponseHeader($header_name, $header_value = NULL, $response_code = NULL, $replace_header = NULL) {
		}

		public static function setResponseCode($code) {
		}

		public static function negotiateLanguage($supported, &$result_array = NULL) {
		}

		public static function negotiateContentType($supported, &$result_array = NULL) {
		}

		public static function negotiateEncoding($supported, &$result_array = NULL) {
		}

		public static function negotiateCharset($supported, &$result_array = NULL) {
		}

		public static function negotiate($params, $supported, $primary_type_separator = NULL, &$result_array = NULL) {
		}

	}

	class Header implements \Serializable 
	{
		const MATCH_LOOSE = 0;
		const MATCH_CASE = 1;
		const MATCH_WORD = 16;
		const MATCH_FULL = 32;
		const MATCH_STRICT = 33;

		public $name;
		public $value;

		public function __construct($name = NULL, $value = NULL) {
		}

		public function serialize() {
		}

		public function __toString() {
		}

		public function toString() {
		}

		public function unserialize($serialized) {
		}

		public function match($value, $flags = NULL) {
		}

		public function negotiate($supported, &$result = NULL) {
		}

		public function getParams($param_sep = NULL, $arg_sep = NULL, $val_sep = NULL, $flags = NULL) {
		}

		public static function parse($string, $header_class = NULL) {
		}

	}

	class Message implements \Countable, \Serializable, \Iterator 
	{
		const TYPE_NONE = 0;
		const TYPE_REQUEST = 1;
		const TYPE_RESPONSE = 2;

		protected $type;
		protected $body;
		protected $requestMethod;
		protected $requestUrl;
		protected $responseStatus;
		protected $responseCode;
		protected $httpVersion;
		protected $headers;
		protected $parentMessage;

		public function __construct($message = NULL, $greedy = NULL) {
		}

		public function getBody() {
		}

		public function setBody(\http\Message\Body $body) {
		}

		public function addBody(\http\Message\Body $body) {
		}

		public function getHeader($header, $into_class = NULL) {
		}

		public function setHeader($header, $value = NULL) {
		}

		public function addHeader($header, $value) {
		}

		public function getHeaders() {
		}

		public function setHeaders(array $headers) {
		}

		public function addHeaders(array $headers, $append = NULL) {
		}

		public function getType() {
		}

		public function setType($type) {
		}

		public function getInfo() {
		}

		public function setInfo($http_info) {
		}

		public function getResponseCode() {
		}

		public function setResponseCode($response_code, $strict = NULL) {
		}

		public function getResponseStatus() {
		}

		public function setResponseStatus($response_status) {
		}

		public function getRequestMethod() {
		}

		public function setRequestMethod($request_method) {
		}

		public function getRequestUrl() {
		}

		public function setRequestUrl($url) {
		}

		public function getHttpVersion() {
		}

		public function setHttpVersion($http_version) {
		}

		public function getParentMessage() {
		}

		public function toString($include_parent = NULL) {
		}

		public function toCallback($callback) {
		}

		public function toStream($stream) {
		}

		public function count() {
		}

		public function serialize() {
		}

		public function unserialize($serialized) {
		}

		public function rewind() {
		}

		public function valid() {
		}

		public function current() {
		}

		public function key() {
		}

		public function next() {
		}

		public function __toString() {
		}

		public function detach() {
		}

		public function prepend(\http\Message $message, $top = NULL) {
		}

		public function reverse() {
		}

		public function isMultipart(&$boundary = NULL) {
		}

		public function splitMultipartBody() {
		}

	}

	class Params implements \ArrayAccess 
	{
		const DEF_PARAM_SEP = ',';
		const DEF_ARG_SEP = ';';
		const DEF_VAL_SEP = '=';
		const COOKIE_PARAM_SEP = '';
		const PARSE_RAW = 0;
		const PARSE_ESCAPED = 1;
		const PARSE_URLENCODED = 4;
		const PARSE_DIMENSION = 8;
		const PARSE_RFC5987 = 16;
		const PARSE_RFC5988 = 32;
		const PARSE_DEFAULT = 17;
		const PARSE_QUERY = 12;

		public $params;
		public $param_sep;
		public $arg_sep;
		public $val_sep;
		public $flags;

		final public function __construct($params = NULL, $param_sep = NULL, $arg_sep = NULL, $val_sep = NULL, $flags = NULL) {
		}

		public function toArray() {
		}

		public function toString() {
		}

		public function __toString() {
		}

		public function offsetExists($name) {
		}

		public function offsetUnset($name) {
		}

		public function offsetSet($name, $value) {
		}

		public function offsetGet($name) {
		}

	}

	class QueryString implements \Serializable, \ArrayAccess, \IteratorAggregate 
	{
		const TYPE_BOOL = 3;
		const TYPE_INT = 1;
		const TYPE_FLOAT = 2;
		const TYPE_STRING = 6;
		const TYPE_ARRAY = 4;
		const TYPE_OBJECT = 5;

		private static $instance;
		private $queryArray;

		final public function __construct($params = NULL) {
		}

		public function toArray() {
		}

		public function toString() {
		}

		public function __toString() {
		}

		public function get($name = NULL, $type = NULL, $defval = NULL, $delete = NULL) {
		}

		public function set($params) {
		}

		public function mod($params = NULL) {
		}

		public function getBool($name, $defval = NULL, $delete = NULL) {
		}

		public function getInt($name, $defval = NULL, $delete = NULL) {
		}

		public function getFloat($name, $defval = NULL, $delete = NULL) {
		}

		public function getString($name, $defval = NULL, $delete = NULL) {
		}

		public function getArray($name, $defval = NULL, $delete = NULL) {
		}

		public function getObject($name, $defval = NULL, $delete = NULL) {
		}

		public function getIterator() {
		}

		public static function getGlobalInstance() {
		}

		public function xlate($from_encoding, $to_encoding) {
		}

		public function serialize() {
		}

		public function unserialize($serialized) {
		}

		public function offsetGet($offset) {
		}

		public function offsetSet($offset, $value) {
		}

		public function offsetExists($offset) {
		}

		public function offsetUnset($offset) {
		}

	}

	class Url 
	{
		const REPLACE = 0;
		const JOIN_PATH = 1;
		const JOIN_QUERY = 2;
		const STRIP_USER = 4;
		const STRIP_PASS = 8;
		const STRIP_AUTH = 12;
		const STRIP_PORT = 32;
		const STRIP_PATH = 64;
		const STRIP_QUERY = 128;
		const STRIP_FRAGMENT = 256;
		const STRIP_ALL = 492;
		const FROM_ENV = 4096;
		const SANITIZE_PATH = 8192;
		const PARSE_MBLOC = 65536;
		const PARSE_MBUTF8 = 131072;
		const PARSE_TOIDN = 1048576;
		const PARSE_TOPCT = 2097152;

		public $scheme;
		public $user;
		public $pass;
		public $host;
		public $port;
		public $path;
		public $query;
		public $fragment;

		public function __construct($old_url = NULL, $new_url = NULL, $flags = NULL) {
		}

		public function mod($more_url_parts, $flags = NULL) {
		}

		public function toString() {
		}

		public function __toString() {
		}

		public function toArray() {
		}

	}
}

namespace http\Client 
{

	class Request extends \http\Message implements \Iterator, \Serializable, \Countable 
	{
		protected $options;

		public function __construct($method = NULL, $url = NULL, array $headers = NULL, \http\Message\Body $body = NULL) {
		}

		public function setContentType($content_type) {
		}

		public function getContentType() {
		}

		public function setQuery($query_data = NULL) {
		}

		public function getQuery() {
		}

		public function addQuery($query_data) {
		}

		public function setOptions(array $options = NULL) {
		}

		public function getOptions() {
		}

		public function setSslOptions(array $ssl_options = NULL) {
		}

		public function getSslOptions() {
		}

		public function addSslOptions(array $ssl_options = NULL) {
		}

	}

	class Response extends \http\Message implements \Iterator, \Serializable, \Countable 
	{
		protected $transferInfo;

		public function getCookies($flags = NULL, $allowed_extras = NULL) {
		}

		public function getTransferInfo($element = NULL) {
		}

	}
}

namespace http\Client\Curl 
{
	const AUTH_ANY = -17;
	const AUTH_BASIC = 1;
	const AUTH_DIGEST = 2;
	const AUTH_DIGEST_IE = 16;
	const AUTH_GSSNEG = 4;
	const AUTH_NTLM = 8;
	const HTTP_VERSION_1_0 = 1;
	const HTTP_VERSION_1_1 = 2;
	const HTTP_VERSION_2_0 = 3;
	const HTTP_VERSION_ANY = 0;
	const IPRESOLVE_ANY = 0;
	const IPRESOLVE_V4 = 1;
	const IPRESOLVE_V6 = 2;
	const POSTREDIR_301 = 1;
	const POSTREDIR_302 = 2;
	const POSTREDIR_303 = 4;
	const POSTREDIR_ALL = 7;
	const PROXY_HTTP = 0;
	const PROXY_HTTP_1_0 = 1;
	const PROXY_SOCKS4 = 4;
	const PROXY_SOCKS4A = 5;
	const PROXY_SOCKS5 = 5;
	const PROXY_SOCKS5_HOSTNAME = 5;
	const SSL_VERSION_ANY = 0;
	const SSL_VERSION_SSLv2 = 2;
	const SSL_VERSION_SSLv3 = 3;
	const SSL_VERSION_TLSv1 = 1;
	const SSL_VERSION_TLSv1_0 = 4;
	const SSL_VERSION_TLSv1_1 = 5;
	const SSL_VERSION_TLSv1_2 = 6;
}

namespace http\Encoding 
{

	abstract class Stream 
	{
		const FLUSH_NONE = 0;
		const FLUSH_SYNC = 1048576;
		const FLUSH_FULL = 2097152;

		public function __construct($flags = NULL) {
		}

		public function update($data) {
		}

		public function flush() {
		}

		public function done() {
		}

		public function finish() {
		}

	}
}

namespace http\Encoding\Stream 
{

	class Dechunk extends \http\Encoding\Stream 
	{
		public static function decode($data, &$decoded_len = NULL) {
		}

	}

	class Deflate extends \http\Encoding\Stream 
	{
		const TYPE_GZIP = 16;
		const TYPE_ZLIB = 0;
		const TYPE_RAW = 32;
		const LEVEL_DEF = 0;
		const LEVEL_MIN = 1;
		const LEVEL_MAX = 9;
		const STRATEGY_DEF = 0;
		const STRATEGY_FILT = 256;
		const STRATEGY_HUFF = 512;
		const STRATEGY_RLE = 768;
		const STRATEGY_FIXED = 1024;

		public static function encode($data, $flags = NULL) {
		}

	}

	class Inflate extends \http\Encoding\Stream 
	{
		public static function decode($data) {
		}

	}
}

namespace http\Env 
{

	class Request extends \http\Message implements \Iterator, \Serializable, \Countable 
	{
		protected $query;
		protected $form;
		protected $cookie;
		protected $files;

		public function __construct() {
		}

		public function getForm($name = NULL, $type = NULL, $defval = NULL, $delete = NULL) {
		}

		public function getQuery($name = NULL, $type = NULL, $defval = NULL, $delete = NULL) {
		}

		public function getCookie($name = NULL, $type = NULL, $defval = NULL, $delete = NULL) {
		}

		public function getFiles() {
		}

	}

	class Response extends \http\Message implements \Iterator, \Serializable, \Countable 
	{
		const CONTENT_ENCODING_NONE = 0;
		const CONTENT_ENCODING_GZIP = 1;
		const CACHE_NO = 0;
		const CACHE_HIT = 1;
		const CACHE_MISS = 2;

		protected $request;
		protected $cookies;
		protected $contentType;
		protected $contentDisposition;
		protected $contentEncoding;
		protected $cacheControl;
		protected $etag;
		protected $lastModified;
		protected $throttleDelay;
		protected $throttleChunk;

		public function __construct() {
		}

		public function __invoke($ob_string, $ob_flags = NULL) {
		}

		public function setEnvRequest(\http\Message $env_request) {
		}

		public function setCookie($cookie) {
		}

		public function setContentType($content_type) {
		}

		public function setContentDisposition(array $disposition_params) {
		}

		public function setContentEncoding($content_encoding) {
		}

		public function setCacheControl($cache_control) {
		}

		public function setLastModified($last_modified) {
		}

		public function isCachedByLastModified($header_name = NULL) {
		}

		public function setEtag($etag) {
		}

		public function isCachedByEtag($header_name = NULL) {
		}

		public function setThrottleRate($chunk_size, $delay = NULL) {
		}

		public function send($stream = NULL) {
		}

	}
}

namespace http\Exception 
{

	class BadConversionException extends \DomainException implements \http\Exception 
	{
	}

	class UnexpectedValueException extends \UnexpectedValueException implements \http\Exception 
	{
	}

	class BadUrlException extends \DomainException implements \http\Exception 
	{
	}

	class BadMethodCallException extends \BadMethodCallException implements \http\Exception 
	{
	}

	class RuntimeException extends \RuntimeException implements \http\Exception 
	{
	}

	class InvalidArgumentException extends \InvalidArgumentException implements \http\Exception 
	{
	}

	class BadMessageException extends \DomainException implements \http\Exception 
	{
	}

	class BadHeaderException extends \DomainException implements \http\Exception 
	{
	}

	class BadQueryStringException extends \DomainException implements \http\Exception 
	{
	}
}

namespace http\Header 
{

	class Parser 
	{
		const CLEANUP = 1;
		const STATE_FAILURE = -1;
		const STATE_START = 0;
		const STATE_KEY = 1;
		const STATE_VALUE = 2;
		const STATE_VALUE_EX = 3;
		const STATE_HEADER_DONE = 4;
		const STATE_DONE = 5;

		public function getState() {
		}

		public function parse($data, $flags, array &$headers) {
		}

		public function stream($stream, $flags, array &$headers) {
		}

	}
}

namespace http\Message 
{

	class Body implements \Serializable 
	{
		public function __construct($stream = NULL) {
		}

		public function __toString() {
		}

		public function toString() {
		}

		public function serialize() {
		}

		public function unserialize($serialized) {
		}

		public function toStream($stream, $offset = NULL, $maxlen = NULL) {
		}

		public function toCallback($callback, $offset = NULL, $maxlen = NULL) {
		}

		public function getResource() {
		}

		public function getBoundary() {
		}

		public function append($string) {
		}

		public function addForm(array $fields = NULL, array $files = NULL) {
		}

		public function addPart(\http\Message $message) {
		}

		public function etag() {
		}

		public function stat($field = NULL) {
		}

	}

	class Parser 
	{
		const CLEANUP = 1;
		const DUMB_BODIES = 2;
		const EMPTY_REDIRECTS = 4;
		const GREEDY = 8;
		const STATE_FAILURE = -1;
		const STATE_START = 0;
		const STATE_HEADER = 1;
		const STATE_HEADER_DONE = 2;
		const STATE_BODY = 3;
		const STATE_BODY_DUMB = 4;
		const STATE_BODY_LENGTH = 5;
		const STATE_BODY_CHUNKED = 6;
		const STATE_BODY_DONE = 7;
		const STATE_UPDATE_CL = 8;
		const STATE_DONE = 9;

		public function getState() {
		}

		public function parse($data, $flags, &$message) {
		}

		public function stream($stream, $flags, &$message) {
		}

	}
}

