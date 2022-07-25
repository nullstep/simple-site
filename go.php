<?php

//     ▄████████   ▄█     █▄      ▄████████  
//    ███    ███  ███     ███    ███    ███  
//    ███    █▀   ███     ███    ███    █▀   
//    ███         ███     ███    ███         
//  ▀███████████  ███     ███  ▀███████████  
//           ███  ███     ███           ███  
//     ▄█    ███  ███ ▄█▄ ███     ▄█    ███  
//   ▄████████▀    ▀███▀███▀    ▄████████▀    

// set errors
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(-1);

// defines
define("_DOMAIN", $_SERVER["HTTP_HOST"]);
define("_EMAIL", "info@" . _DOMAIN);
define("_DESC", "site description");

define("_SALT", "9ds8frg6isuhrtioushew498tyap98sye");
define("_HASH", hash("sha256", _DOMAIN . _SALT));

define("_USEDB", FALSE);
define("_CACHE", FALSE);

define("_NAV", [
	[
		"name" => "/"
	],
	[
		"name" => "/page-one"
	],
	[
		"name" => "/page-two"
	],
	[
		"name" => "/-hidden-page"
	],
	[
		"name" => "/#parent-menu",
		"children" => [
			[
				"name" => "/child-page-one"
			],
			[
				"name" => "/child-page-two"
			]
		]
	],
	[
		"name" => "/page-three"
	],
	[
		"name" => "/page-four"
	]
]);

define("_DATA", [
]);

// vars
$GLOBALS["code"] = "200";

$fields = [
	"name",
	"email_address",
	"phone_number",
	"text"
];

// return mime type
function _mime($type) {
	$mimes = [
		'js' => 'application/javascript',
		'ico' => 'image/x-icon',
		'svg' => 'image/svg+xml',
		'jpg' => 'image/jpeg',
		'png' => 'image/png',
		'gif' => 'image/gif',
		'css' => 'text/css',
		'txt' => 'text/plain',
		'html' => 'text/html',
		'ttf' => 'font/truetype',
		'otf' => 'font/opentype',
		'eot' => 'application/vnd.ms-fontobject',
		'woff' => 'application/font-woff',
		'woff2' => 'font/woff2'
	];
	return $mimes[$type];
}

// output content
function _out($status, $mime, $content) {
	switch ($status) {
		case "200":
			header("Status: 200 OK", true, 200);
			break;
		case "404":
			header("Status: 404 Not Found", true, 404);
			break;
		case "500":
			header("Status: 500 Server Error", true, 500);
			break;
	}

	header("Content-Type: " . _mime($mime));
	header("Content-Length: " . strlen($content));
	echo $content;
}

// return file if it exists
function _load($file) {
	return (file_exists($file)) ? file_get_contents($file) : "";
}

// return 404 page
function _404() {
	$GLOBALS["code"] = "404";
	$file = _load("./html/404.html");
	$html = "<html><body><h1>404</h1><p>Page not found :(</p></body></html>";
	return ($file == "") ? $html : $file;
}

// parse url and return object
function _parse_path($path) {
	$url = (ltrim($path, "/")) ?: "index";
	$parts = explode("/", $url);
	$parsed = parse_url($url);
	$info = pathinfo($parsed["path"]);

	$ext = (isset($info["extension"])) ?: "";
	$filename = $info["filename"];

	return (object) [
		"path" => $path,
		"url" => $url,
		"info" => $info,
		"ext" => $ext,
		"filename" => $filename
	];
}

// check if cache clear request
function _is_cc() {
	if (isset($_GET["cc"]) && ($_GET["cc"] === "yes")) {
		$files = glob("./cache/*.html");
		if (count($files) > 0) {
			foreach ($files as $file) {
				unlink($file);
			}
		}
		return TRUE;
	}
}

// check if form post
function _is_form() {
	return (isset($_POST["hash"]) && $_POST["hash"] == _HASH) ? TRUE : FALSE;
}

//     ███        ▄█    █▄       ▄████████   ▄▄▄▄███▄▄▄▄      ▄████████ 
// ▀█████████▄   ███    ███     ███    ███ ▄██▀▀▀███▀▀▀██▄   ███    ███ 
//    ▀███▀▀██   ███    ███     ███    █▀  ███   ███   ███   ███    █▀  
//     ███   ▀  ▄███▄▄▄▄███▄▄  ▄███▄▄▄     ███   ███   ███  ▄███▄▄▄     
//     ███     ▀▀███▀▀▀▀███▀  ▀▀███▀▀▀     ███   ███   ███ ▀▀███▀▀▀     
//     ███       ███    ███     ███    █▄  ███   ███   ███   ███    █▄  
//     ███       ███    ███     ███    ███ ███   ███   ███   ███    ███ 
//    ▄████▀     ███    █▀      ██████████  ▀█   ███   █▀    ██████████

// recursive theme parser
function _theme($params, $html = "") {
	$root = __DIR__;

	if (isset($params["top"])) {
		$page = $params["url"];
		$filename = "{$root}/html/{$page}.html";
		$html = _load($filename);

		if ($html == "") {
			$html = _404();
		}

		unset($params["top"]);
	}

	$array = [];
	preg_match_all("/\[\[(.+?)\]\]/", $html, $array);
	$tags = (isset($array[0])) ? $array[0] : [];
	$cmds = (isset($array[1])) ? $array[1] : [];
	$count = count($cmds);

	if ($count > 0) {
		for ($i = 0; $i < $count; $i++) {
			$cmd = explode(":", $cmds[$i]);

			switch ($cmd[0]) {
				case "domain": {
					$html = str_replace(
						$tags[$i],
						_DOMAIN,
						$html
					);

					break;
				}
				case "host": {
					$html = str_replace(
						$tags[$i],
						$_SERVER["HTTP_HOST"],
						$html
					);

					break;
				}
				case "ip": {
					$html = str_replace(
						$tags[$i],
						$_SERVER["REMOTE_ADDR"],
						$html
					);

					break;
				}
				case "title": {
					$title = "";
					$html = str_replace(
						$tags[$i],
						ucfirst($title),
						$html
					);

					break;
				}
				case "description": {
					$html = str_replace(
						$tags[$i],
						_DESC,
						$html
					);

					break;
				}
				case "year": {
					$html = str_replace(
						$tags[$i],
						date("Y"),
						$html
					);

					break;
				}
				case "hash": {
					$html = str_replace(
						$tags[$i],
						_HASH,
						$html
					);

					break;
				}
				case "nav": {
					$menu = "";

					$file = "{$root}/html/_nav.html";
					$nav = (file_exists($file)) ? file_get_contents($file) : "";
					$file = "{$root}/html/_np1.html";
					$n1p = (file_exists($file)) ? file_get_contents($file) : "";
					$file = "{$root}/html/_nc1.html";
					$n1c = (file_exists($file)) ? file_get_contents($file) : "";
					$file = "{$root}/html/_nc2.html";
					$n2c = (file_exists($file)) ? file_get_contents($file) : "";
					$first1 = TRUE;

					foreach (_NAV as $item) {
						$type_1 = $item["name"][0];

						if ($item["name"] == '/') {
							$url_1 = "/";
							$text_1 = "Home";
						}
						else {
							$url_1 = substr($item["name"], 1);
							$text_1 = ucwords(preg_replace("/[\-_]/", " ", $url_1));
						}

						switch ($type_1) {
							case '/': {
								$nl1 = (!$first1) ? "\n" : "";
								$menu .= $nl1 . str_replace(
									"[[url]]",
									$url_1,
									str_replace(
										"[[text]]",
										$text_1,
										$n1c
									)
								);
								$first1 = FALSE;
								break;
							}
							case '#': {
								$kids = "";
								$first2 = TRUE;

								foreach ($item["children"] as $child) {
									$nl2 = (!$first2) ? "\n" : "";
									$type_2 = $child["name"][0];
									$url_2 = substr($child["name"], 1);
									$text_2 = ucwords(preg_replace("/[\-_]/", " ", $url_2));
									$kids .= $nl2 . str_replace(
										"[[url]]",
										$url_2,
										str_replace(
											"[[text]]",
											$text_2,
											$n2c
										)
									);
									$first2 = FALSE;
								}
								$menu .= "\n" . str_replace(
									"[[items]]",
									$kids,
									str_replace(
										"[[text]]",
										$text_1,
										$n1p
									)
								);
								break;
							}
						}
					}
					$html = str_replace(
						$tags[$i],
						str_replace(
							"[[items]]",
							$menu,
							$nav
						),
						$html
					);

					break;
				}
				case "file": {
					$file = "{$root}/html/{$cmd[1]}.html";
					$content = _load($file);

					$html = _theme(
						$params,
						str_replace(
							$tags[$i],
							$content,
							$html
						)
					);

					break;
				}
				case "loop": {
					$loop = "";
					$file = "{$root}/html/{$cmd[3]}.html";
					$block = _load($file);
					$first = TRUE;

					foreach (_DATA as $content) {
						$nl = (!$first) ? "\n" : "";
						if ($cmd[1] == 'yes') {
							$content = strip_tags($content);
						}
						$loop .= $nl . str_replace(
							"[[content]]",
							$content,
							$block
						);
						$first = FALSE;
					}

					$html = _theme(
						$params,
						str_replace(
							$tags[$i],
							$loop,
							$html
						)
					);

					break;
				}
				case "random": {
					$range = explode("-", $cmd[1]);
					$number = mt_rand($range[0], $range[1]);
					$value = (isset($cmd[2]) ? str_pad($number, $cmd[2], "0", STR_PAD_LEFT) : $number);
					$html = str_replace($tags[$i], $value, $html);

					break;
				}
			}
		}
	}

	return $html;
}

//    ▄████████  ▄██████▄  ███    █▄      ███        ▄████████    ▄████████ 
//   ███    ███ ███    ███ ███    ███ ▀█████████▄   ███    ███   ███    ███ 
//   ███    ███ ███    ███ ███    ███    ▀███▀▀██   ███    █▀    ███    ███ 
//  ▄███▄▄▄▄██▀ ███    ███ ███    ███     ███   ▀  ▄███▄▄▄      ▄███▄▄▄▄██▀ 
// ▀▀███▀▀▀▀▀   ███    ███ ███    ███     ███     ▀▀███▀▀▀     ▀▀███▀▀▀▀▀   
// ▀███████████ ███    ███ ███    ███     ███       ███    █▄  ▀███████████ 
//   ███    ███ ███    ███ ███    ███     ███       ███    ███   ███    ███ 
//   ███    ███  ▀██████▀  ████████▀     ▄████▀     ██████████   ███    ███

// get/post request router
function _router($get, $post) {
	$data = (!empty($post)) ? (object)$post : (object)$get;

	if (!isset($data->path)) {
		$data->path ="/";
	}

	$info = _parse_path($data->path);
	$file = "./cache/" . $info->filename . ".html";

	if (file_exists($file) && _CACHE) {
		$html = file_get_contents($file);
	}
	else {
		$html = _theme([
			"page" => $info->filename . $info->ext,
			"url" => "/" . $info->url,
			"top" => TRUE
		]);

		if (($GLOBALS["code"] == 200) && _CACHE) {
			file_put_contents($file, $html);
		}
	}
	
	_out($GLOBALS["code"], "html", $html);
}

//     ▄██████▄    ▄██████▄   
//    ███    ███  ███    ███  
//    ███    █▀   ███    ███  
//   ▄███         ███    ███  
//  ▀▀███ ████▄   ███    ███  
//    ███    ███  ███    ███  
//    ███    ███  ███    ███  
//    ████████▀    ▀██████▀   

// do stuff
if (_is_form()) {
	// contact form post
	$message = "Website Form Submission from " . _DOMAIN . "\n\n";

	foreach ($fields as $field) {
		$title = ucwords(str_replace("_", " ", $field));
		$value = (isset($_POST[$field])) ? $_POST[$field] : "No {$title} Supplied";
		$message .= "{$title}: {$value}\n";
	}

	$ok = mail(
		_EMAIL,
		"Website Contact Form",
		$message,
		["From" => "no-reply@" . _DOMAIN],
		"-fno-reply@" . _DOMAIN
	);

	header("Location: /thanks?{$ok}");
}
else if (_is_cc()) {
	// clear cache request
	header("Location: /?done");
}
else {
	// normal web request
	_router($_GET, $_POST);
}

// EOF