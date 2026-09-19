import fs from 'node:fs';import path from 'node:path';import zlib from 'node:zlib';
const root=process.cwd(),manifest=JSON.parse(fs.readFileSync(path.join(root,'public/legacy/ic3/manifest.json'),'utf8').replace(/^\uFEFF/,''));
const assetRe=/data\/[A-Za-z0-9_./%()+ -]+\.(?:js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|mp3|mp4|m4a|ogg|wav|xml|json)/gi;
const refs=text=>[...new Set([...text.replaceAll('\\/','/').matchAll(assetRe)].map(m=>m[0].replace(/[?#].*$/,'')))];
const quizAssetRe=/quiz\d+[\\/][A-Za-z0-9_./%()+ \\-]+\.(?:js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|mp3|mp4|m4a|ogg|wav|xml|json)/gi;
const embeddedRefs=text=>{
  const found=[];
  for(const match of text.matchAll(/var quizInfo = "([^"]+)"/g)){
    try{
      const quizJson=Buffer.from(match[1],'base64').toString('utf8');
      for(const asset of quizJson.match(quizAssetRe)??[])found.push(`data/${asset.replaceAll('\\\\','/')}`);
    }catch{}
  }
  return [...new Set(found)];
};
let downloaded=0,failed=0;
for(const item of manifest){const localBase=path.join(root,'public',item.local_path.replace(/^\//,'').replace(/\/index\.html$/,''));const index=fs.readFileSync(path.join(localBase,'index.html'),'utf8');const m=index.match(/var presInfo = "([^"]+)"/);if(!m){console.log('SKIP',item.slug);continue}const decoded=zlib.inflateSync(Buffer.from(m[1],'base64')).toString('utf8');const queue=[...refs(decoded),...refs(index)].filter(x=>!['data/player.js','data/browsersupport.js'].includes(x));const seen=new Set();const base=new URL('./',item.source_url);while(queue.length){const rel=queue.shift();if(seen.has(rel))continue;seen.add(rel);const target=path.join(localBase,...rel.split('/'));try{let buf;if(fs.existsSync(target))buf=fs.readFileSync(target);else{const response=await fetch(new URL(rel,base));if(!response.ok)throw new Error(String(response.status));buf=Buffer.from(await response.arrayBuffer());fs.mkdirSync(path.dirname(target),{recursive:true});fs.writeFileSync(target,buf);downloaded++}if(/\.(?:js|css|json|xml)$/i.test(rel)){const text=buf.toString('utf8');for(const child of [...refs(text),...embeddedRefs(text)])if(!seen.has(child))queue.push(child)}}catch(error){failed++;console.log('MISS',item.slug,rel,error.message)}}console.log('OK',item.grade,item.slug,seen.size)}
console.log(JSON.stringify({courses:manifest.length,downloaded,failed}));
