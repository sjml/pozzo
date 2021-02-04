const sirv = require("sirv");
const polka = require("polka");
const proxy = require("http-proxy-middleware");
const compress = require('compression');



let phpConf = "../scripts/configs/no-debug.php.ini";
if (process.argv.includes("debug")) {
  phpConf = "../scripts/configs/debug.php.ini";
}

server = require('child_process').spawn('php', [
    '-c', phpConf,
    '-S', '0.0.0.0:8080',
    '-t', '../public/api'
  ], {
  env: Object.assign({PHP_CLI_SERVER_WORKERS: 4}, process.env),
  stdio: ['ignore', 'inherit', 'inherit'],
  shell: true
});



const frontend = sirv("./public");
const static = sirv("../public");
const php = proxy.createProxyMiddleware("/api", {
  changeOrigin: true,
  logLevel: "debug",
  target: "http://0.0.0.0:8080",
  pathRewrite: {
    "^/api": ""
  }
});




polka()
  .use(php)
  .use(compress(), frontend)
  .use(compress(), static)
  .listen(3000, err => {
    if (err) throw err;
    console.log("Hosting on 3000!!!");
  })
;
