process.env.LAMBDA_TASK_ROOT = process.env.LAMBDA_TASK_ROOT || "/var/task";

const orig = require("./launcher-orig.js");

const isNodeHttp = (req, res) =>
  req && typeof req.on === "function" && typeof req.url === "string" &&
  res && typeof res.end === "function";

function readBody(req) {
  return new Promise((resolve, reject) => {
    if (req.readableEnded) return resolve(undefined);
    const chunks = [];
    req.on("data", (c) => chunks.push(Buffer.isBuffer(c) ? c : Buffer.from(c)));
    req.on("end", () => resolve(chunks.length ? Buffer.concat(chunks) : undefined));
    req.on("error", reject);
  });
}

async function handleNodeHttp(req, res) {
  const headers = req.headers || {};
  const body = await readBody(req);
  const out = await orig.launcher({
    httpMethod: req.method || "GET",
    path: req.url,
    host: headers["x-forwarded-host"] || headers.host,
    headers,
    body,
  });
  const buf = Buffer.from(out.body || "", "base64");
  res.statusCode = out.statusCode || 200;
  for (const [k, v] of Object.entries(out.headers || {})) {
    const key = k.toLowerCase();
    if (key === "transfer-encoding" || key === "connection" || key === "content-length") continue;
    try { res.setHeader(k, v); } catch (_) {}
  }
  res.setHeader("content-length", buf.length);
  res.end(buf);
}

async function handleEvent(event, context) {
  const e = { ...event };
  if (e.Action !== "Invoke") {
    const h = e.headers || {};
    if (Array.isArray(e.body)) e.body = Buffer.from(e.body);
    else if (typeof e.body === "string") e.body = Buffer.from(e.body, e.isBase64Encoded ? "base64" : "utf8");
    else e.body = undefined;
    e.host = e.host || h["x-forwarded-host"] || h.host;
    e.httpMethod = e.httpMethod || e.method;
    e.path = e.path || e.url || e.rawPath;
  }
  return orig.launcher(e, context);
}

const launcher = async (a, b) => {
  try {
    if (isNodeHttp(a, b)) {
      console.log("BRIDGE mode=http", a.method);
      return await handleNodeHttp(a, b);
    }
    console.log("BRIDGE mode=event keys=" + Object.keys(a || {}).join(","));
    return await handleEvent(a, b);
  } catch (err) {
    console.error(
  "BRIDGE error",
  err instanceof Error ? err.name : "UnknownError"
);
    if (b && typeof b.end === "function" && !b.headersSent) {
      b.statusCode = 500;
      b.end("Bridge error");
    } else {
      throw err;
    }
  }
};

module.exports = launcher;
module.exports.launcher = launcher;