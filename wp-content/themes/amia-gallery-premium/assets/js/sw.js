const CACHE='amia-gallery-premium-v1';
self.addEventListener('install',e=>{e.waitUntil(caches.open(CACHE).then(c=>c.addAll(['/'])))});
self.addEventListener('fetch',e=>{e.respondWith(caches.match(e.request).then(r=>r||fetch(e.request).then(resp=>{if(e.request.method==='GET'){const copy=resp.clone();caches.open(CACHE).then(c=>c.put(e.request,copy));}return resp}).catch(()=>r)))});
