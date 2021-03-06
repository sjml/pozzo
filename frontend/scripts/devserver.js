const polka = require("polka");
const sirv = require("sirv");
const proxy = require("http-proxy-middleware");
const compress = require("compression");
const colors = require("kleur");



const debug = process.argv.includes("debug");
const noFront = process.argv.includes("no_front");
const runSlow = process.argv.includes("slow")


function displayCode(statusCode) {
  if (statusCode < 300) {
    return colors.bgGreen().black(`[${statusCode}]`);
  }
  if (statusCode < 400) {
    return colors.bgYellow().black(`[${statusCode}]`);
  }
  return colors.bgRed().black(`[${statusCode}]`);
}

const phpProcessMap = {};
const phpMatcher = new RegExp("^\\[([0-9]+)\\] \\[[^\\]]*\\](.*)$");
const phpLaunchedMatcher = new RegExp("^PHP [0-9\\.]+ Development Server .*\\) started$");
const phpProcessMatcher = new RegExp("^[0-9.:]+ \\[([0-9]+)\\]: (.*)$");
function processPHPOutput(data) {
  const dataStr = data.toString().trim();
  const messages = dataStr.split("\n");
  messages.forEach((m) => {
    const match = m.match(phpMatcher);
    if (match == null) {
      console.log(colors.bgRed("PHP ERROR: ") + " " + m);
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
      const outputMatch = message.match(phpProcessMatcher);
      if (!outputMatch) {
        console.log(colors.bgRed("PHP ERROR: ") + " " + message);
      }
      else {
        console.log(colors.green(`   PHP${debug?"_D":""}@${phpProcessMap[processID]}::`) + displayCode(outputMatch[1]) + ` ${outputMatch[2]}`);
      }
    }
  });
}



let phpConf = "../scripts/configs/no-debug.php.ini";
if (debug) {
  phpConf = "../scripts/configs/debug.php.ini";
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




function wrapStatic(name, dir, options) {
  const sirver = sirv(dir, options);
  return (req, res, next) => {
    sirver(req, res, next);
    if (res.headersSent && (!res.pozzoDevSent)) {
      res.pozzoDevSent = true;
      console.log(colors.green(`${name}::`) + `${displayCode(res.statusCode)} ${req.originalUrl || req.url}`);
    }
  };
}

const frontend = wrapStatic("Frontend", "./public", {dev: true});
const static   = wrapStatic("  Static", "../public", {dev: true, single: true});



function randomNormal() {
  // random value with normal distribution
  let u = 0, v = 0;
  while (u == 0) u = Math.random();
  while (v == 0) v = Math.random();
  let num = Math.sqrt(-2.0 * Math.log(u)) * Math.cos(2.0 * Math.PI * v);
  num = num / 10.0 + 0.5;
  if (num > 1 || num < 0) {
    return randomNormal();
  }
  return num;
}

function slow(req, res, next) {
  const delay = 1000;
  const variance = 400;


  const rand = randomNormal();
  const min = Math.ceil(delay - variance);
  const max = Math.floor(delay + variance);
  const wait = Math.floor(rand * (max - min + 1)) + min;

  setTimeout(next, wait);
}



const port = 3000;
let devServer = polka();

devServer = devServer.use(compress());
if (runSlow) {
  devServer = devServer.use(slow);
}
devServer = devServer.use(php);
if (!noFront) {
  devServer = devServer.use(frontend);
}
devServer = devServer.use(static);

const listener = devServer.listen(port, err => {
  if (err) throw err;
  console.log(colors.green(`\n🚀 Hosting on http://0.0.0.0:${port} ${noFront ? "(static only)":""}`));
});

function shutdownAll() {
  console.log(colors.yellow("\n⚡️ Shutting down..."));
  colors.reset();
  if (listener) {
    console.log(colors.blue("Shutting down frontend proxies"));
    listener.server.close();
  }
  if (phpServer) {
    console.log(colors.blue("Shutting down PHP"));
    phpServer.kill(0);
  }
  process.exit();
}

process.on('SIGINT', shutdownAll);


