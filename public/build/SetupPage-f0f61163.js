import{S as e,i as s,s as t,O as a,c as i,m as n,t as l,a as u,d as r,b as o,e as c,l as d,o as p,f,g as v,h as y,j as h,k as x,n as b,p as m,q as $,r as g,u as w,v as P,w as S,x as T,R as j,y as k,z as N}from"./main-7b06f501.js";function O(e){let s,t,a,i,n,l,u,r,o,c,d,p,f,j,k,N,O,_,q,z,C,M,R,U,V,A,B;return{c(){s=v("div"),t=v("form"),a=v("label"),i=y("Site Name:\n                "),n=v("input"),l=h(),u=v("label"),r=y("User:\n                "),o=v("input"),c=h(),d=v("label"),p=y("Password:\n                "),f=v("input"),j=h(),k=v("label"),N=y("Verify Password:\n                "),O=v("input"),_=h(),q=v("button"),z=y("Configure Site"),M=h(),R=v("div"),U=y(e[6]),V=y(" "),x(n,"type","text"),n.disabled=e[5],x(n,"class","svelte-1xreyiu"),x(a,"class","svelte-1xreyiu"),x(o,"type","text"),o.disabled=e[5],x(o,"class","svelte-1xreyiu"),x(u,"class","svelte-1xreyiu"),x(f,"type","password"),f.disabled=e[5],x(f,"class","svelte-1xreyiu"),x(d,"class","svelte-1xreyiu"),x(O,"type","password"),O.disabled=e[5],x(O,"class","svelte-1xreyiu"),x(k,"class","svelte-1xreyiu"),x(q,"type","submit"),q.disabled=C=e[5]||!e[7],x(q,"class","svelte-1xreyiu"),x(R,"class","configMessage svelte-1xreyiu"),x(s,"class","configPrompt svelte-1xreyiu")},m(v,y){b(v,s,y),m(s,t),m(t,a),m(a,i),m(a,n),e[10](n),$(n,e[0]),m(t,l),m(t,u),m(u,r),m(u,o),$(o,e[1]),m(t,c),m(t,d),m(d,p),m(d,f),$(f,e[2]),m(t,j),m(t,k),m(k,N),m(k,O),$(O,e[3]),m(t,_),m(t,q),m(q,z),m(s,M),m(s,R),m(R,U),m(R,V),A||(B=[g(n,"input",e[11]),g(o,"input",e[12]),g(f,"input",e[13]),g(O,"input",e[14]),g(t,"submit",w(e[8]))],A=!0)},p(e,s){32&s&&(n.disabled=e[5]),1&s&&n.value!==e[0]&&$(n,e[0]),32&s&&(o.disabled=e[5]),2&s&&o.value!==e[1]&&$(o,e[1]),32&s&&(f.disabled=e[5]),4&s&&f.value!==e[2]&&$(f,e[2]),32&s&&(O.disabled=e[5]),8&s&&O.value!==e[3]&&$(O,e[3]),160&s&&C!==(C=e[5]||!e[7])&&(q.disabled=C),64&s&&P(U,e[6])},d(t){t&&S(s),e[10](null),A=!1,T(B)}}}function _(e){let s,t;return s=new a({props:{$$slots:{default:[O]},$$scope:{ctx:e}}}),{c(){i(s.$$.fragment)},m(e,a){n(s,e,a),t=!0},p(e,[t]){const a={};131327&t&&(a.$$scope={dirty:t,ctx:e}),s.$set(a)},i(e){t||(l(s.$$.fragment,e),t=!0)},o(e){u(s.$$.fragment,e),t=!1},d(e){r(s,e)}}}function q(e,s,t){let a,i;o(e,c,(e=>t(9,a=e))),o(e,d,(e=>t(15,i=e)));var n=this&&this.__awaiter||function(e,s,t,a){return new(t||(t=Promise))((function(i,n){function l(e){try{r(a.next(e))}catch(e){n(e)}}function u(e){try{r(a.throw(e))}catch(e){n(e)}}function r(e){var s;e.done?i(e.value):(s=e.value,s instanceof t?s:new t((function(e){e(s)}))).then(l,u)}r((a=a.apply(e,s||[])).next())}))};let l;p((()=>{l.focus()}));let u=!1,r="",v="",y="",h="",x="";let b=!1;return e.$$.update=()=>{512&e.$$.dirty&&!1!==a.siteTitle&&f.goto("/"),15&e.$$.dirty&&(r.length>0&&v.length>0&&y.length>0&&y==h?t(7,b=!0):t(7,b=!1))},[r,v,y,h,l,u,x,b,function(){return n(this,void 0,void 0,(function*(){t(5,u=!0);const e=yield j("/setup",{params:{siteTitle:r,userName:v,password:y},method:"POST"});e.success?(k(c,a.siteTitle=r,a),k(d,i=e.data.key,i),f.goto("/")):t(6,x="Something went wrong. :("),t(5,u=!1)}))},a,function(e){N[e?"unshift":"push"]((()=>{l=e,t(4,l)}))},function(){r=this.value,t(0,r)},function(){v=this.value,t(1,v)},function(){y=this.value,t(2,y)},function(){h=this.value,t(3,h)}]}export default class extends e{constructor(e){super(),s(this,e,q,_,t,{})}}
