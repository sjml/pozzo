const sirv = require("sirv");
const polka = require("polka");
const proxy = require("http-proxy-middleware");
const compress = require('compression');
const colors = require('kleur');



const debug = process.argv.includes("debug");
const noFront = process.argv.includes("no_front");



let phpConf = "../scripts/configs/no-debug.php.ini";
if (debug) {
  phpConf = "../scripts/configs/debug.php.ini";
}

const phpProcessMap = {};
const phpMatcher = new RegExp("^\\[([0-9]+)\\] \\[[^\\]]*\\](.*)$");
const phpLaunchedMatcher = new RegExp("^PHP [0-9\\.]+ Development Server .*\\) started$");
function processPHPOutput(data) {
  const dataStr = data.toString().trim();
  const messages = dataStr.split("\n");
  messages.forEach((m) => {
    const match = m.match(phpMatcher);
    if (match == null) {
      console.error("Couldn't parse PHP output:", m);
      return;
    }
    const processID = match[1];
    const message = match[2].trim();
    if (message.match(phpLaunchedMatcher)) {
      phpProcessMap[processID] = Object.keys(phpProcessMap).length;
      console.log(colors.blue(`Started PHP ${debug?"debug ":""}process ${phpProcessMap[processID]} : ${processID}`));
    }
    else {
      if (message.endsWith("Accepted") || message.endsWith("Closing")) {
        return;
      }
      console.log(colors.green(`PHP${debug?"_D":""}@${phpProcessMap[processID]}:: `) + message.replace(/^[0-9.:]+ /, ""));
    }
  });
}

const phpServer = require('child_process').spawn('php', [
    '-c', phpConf,
    '-S', '0.0.0.0:8080',
    '-t', '../public/api'
  ], {
  env: Object.assign({PHP_CLI_SERVER_WORKERS: 4}, process.env),
  stdio: ['ignore', 'pipe', 'pipe'],
  shell: true
});
phpServer.stdout.on("data", (data) => processPHPOutput(data));
phpServer.stderr.on("data", (data) => processPHPOutput(data));


const php = proxy.createProxyMiddleware("/api", {
  changeOrigin: true,
  logLevel: "silent",
  target: "http://0.0.0.0:8080",
  pathRewrite: {
    "^/api": ""
  }
});




function wrapSirv(name, dir, options) {
  const sirver = sirv(dir, options);
  // console.log(sirver.)
  return (req, res, next) => {
    sirver(req, res, next);
    console.log(colors.green(`${name}::`) + `[${res.statusCode}]: ${req.originalUrl || req.url}`);
  };
}

const frontend = wrapSirv("Frontend", "./public", {dev: true});
const static = wrapSirv("Static", "../public", {dev: true, single: true});



const port = 3000;
let polkaInstance = polka();

polkaInstance = polkaInstance.use(php);
if (!noFront) {
  polkaInstance = polkaInstance.use(compress(), frontend);
}
polkaInstance = polkaInstance.use(compress(), static);

const listener = polkaInstance.listen(port, err => {
  if (err) throw err;
  console.log(colors.green(`\n🚀 Hosting on http://0.0.0.0:${port} ${noFront ? "(static only)":""}`));
});

function shutdownAll() {
  console.log(colors.yellow("\n⚡️ Shutting down..."));
  if (listener) {
    console.log(colors.blue("Shutting down frontend proxies"));
    listener.server.close();
  }
  if (phpServer) {
    console.log(colors.blue("Shutting down PHP"));
    phpServer.kill(0);
  }
}

process.on('SIGINT', shutdownAll);


