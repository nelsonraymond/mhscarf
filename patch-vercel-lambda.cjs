const fs = require("fs");
const path = require("path");

const dir = ".vercel/output/functions/api/index.func";
const configPath = path.join(dir, ".vc-config.json");
const config = JSON.parse(fs.readFileSync(configPath, "utf8"));

if (config.handler !== "launcher.launcher") {
  throw new Error(`Handler tidak sesuai dugaan: ${config.handler}`);
}

const map = config.filePathMap || {};
const originalTarget = map["launcher.js"];

if (!originalTarget) {
  throw new Error("Mapping launcher.js asli tidak ditemukan.");
}

if (map["launcher-orig.js"]) {
  throw new Error("Mapping launcher-orig.js sudah ada; build ulang dahulu.");
}

map["launcher-orig.js"] = originalTarget;
map["launcher.js"] = "launcher.js";

for (const [key, value] of Object.entries(map)) {
  const base = path.basename(key);
  if (
    key.includes(".git/") ||
    value.includes(".git/") ||
    key === "user/public/storage" ||
    base === ".env" ||
    base.startsWith(".env.")
  ) {
    delete map[key];
  }
}

const bridge = `
process.env.LAMBDA_TASK_ROOT =
  process.env.LAMBDA_TASK_ROOT || "/var/task";

const orig = require("./launcher-orig.js");

exports.launcher = async (event, context) => {
  const e = { ...event };
  const h = e.headers || {};

  if (Array.isArray(e.body)) {
    e.body = Buffer.from(e.body);
  } else if (typeof e.body === "string") {
    e.body = Buffer.from(
      e.body,
      e.isBase64Encoded ? "base64" : "utf8"
    );
  } else {
    e.body = undefined;
  }

  e.host = e.host || h["x-forwarded-host"] || h.host;
  return orig.launcher(e, context);
};
module.exports = exports.launcher;
module.exports.launcher = exports.launcher;
`.trimStart();

fs.writeFileSync(path.join(dir, "launcher.js"), bridge);
config.handler = "launcher.js";
config.filePathMap = map;
fs.writeFileSync(configPath, JSON.stringify(config, null, 2) + "\n");

console.log("Bridge dibuat:", fs.existsSync(path.join(dir, "launcher.js")));
console.log("Handler:", config.handler);
console.log("Mapping launcher.js:", map["launcher.js"]);
console.log("Mapping launcher-orig.js:", map["launcher-orig.js"]);
