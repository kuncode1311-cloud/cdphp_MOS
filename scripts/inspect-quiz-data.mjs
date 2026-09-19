import fs from 'node:fs';
import path from 'node:path';

const root=process.cwd();
const manifest=JSON.parse(fs.readFileSync(path.join(root,'public/legacy/ic3/manifest.json'),'utf8').replace(/^\uFEFF/,''));
for(const item of manifest){
  const base=path.join(root,'public',item.local_path.replace(/^\//,'').replace(/\/index\.html$/,''));
  const source=fs.readFileSync(path.join(base,'data/quiz1.js'),'utf8');
  const match=source.match(/var quizInfo = "([^"]+)"/);
  const quiz=JSON.parse(Buffer.from(match[1],'base64').toString('utf8'));
  if(process.argv.includes('--counts')){
    console.log(item.slug,quiz.d.sl.g[0].S.length);
    continue;
  }
  if(process.argv.includes('--types')){
    const types={}; for(const slide of quiz.d.sl.g[0].S)types[slide.tp]=(types[slide.tp]??0)+1;
    console.log(item.slug,JSON.stringify(types));
    continue;
  }
  if(process.argv.includes('--sample')){
    console.log(JSON.stringify(quiz,null,2).slice(0,25000));
    break;
  }
  if(process.argv.includes('--slides')){
    console.log(JSON.stringify(quiz.d.sl.g[0].S.slice(0,3),null,2));
    break;
  }
  if(process.argv.includes('--resources')){
    console.log(JSON.stringify(quiz.rs,null,2).slice(0,12000));
    break;
  }
  if(process.argv.includes('--shapes')){
    const representatives={}; for(const slide of quiz.d.sl.g[0].S)if(!representatives[slide.tp])representatives[slide.tp]={tp:slide.tp,D:slide.D,C:slide.C};
    console.log(JSON.stringify(representatives,null,2).slice(0,30000)); break;
  }
  const counts=[];
  const walk=(value,key='root')=>{
    if(Array.isArray(value)){
      if(value.length>=5)counts.push([key,value.length,Object.keys(value[0]??{}).join(',')]);
      value.forEach((entry,index)=>walk(entry,`${key}[${index}]`));
    }else if(value&&typeof value==='object')for(const [childKey,entry] of Object.entries(value))walk(entry,`${key}.${childKey}`);
  };
  walk(quiz);
  console.log(item.grade,item.slug,counts.slice(0,12));
}
