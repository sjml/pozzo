import{S as t,i as e,s as n,x as s,y as o,b as l,v as a,h as r,j as i,z as c,A as p,G as u,e as d,g as f,a as g,f as m,t as h,k as w,B as v,o as $,p as k,n as y,T as z,F as x,r as L,w as C,c as b,C as j,m as I,D as M,H as S,d as N,E as q,I as O,J as T,K as F}from"./main-25e07129.js";import{P}from"./PhotoMap-246d56ac.js";function _(t){let e,n;return{c(){e=s("video"),o(e,"class","main svelte-18nqc1q"),e.src!==(n=t[0])&&o(e,"src",n),e.controls=!0,o(e,"poster",t[1])},m(t,n){l(t,e,n)},p(t,[s]){1&s&&e.src!==(n=t[0])&&o(e,"src",n),2&s&&o(e,"poster",t[1])},i:a,o:a,d(t){t&&r(e)}}}function D(t,e,n){let s,o,l;return i(t,c,(t=>n(2,s=t))),p((()=>{n(1,l=u("large",s.hash,s.uniq));const t=s.originalFilename.split(".").pop();n(0,o=u("orig",s.hash,s.uniq,t))})),[o,l]}class A extends t{constructor(t){super(),e(this,t,D,_,n,{})}}function B(t,e,n){const s=t.slice();return s[7]=e[n],s}function G(t){let e,n,a,i,c,p;const u=[E,V],w=[];function v(t,e){return t[0].isVideo?1:0}n=v(t),a=w[n]=u[n](t);let $=t[1]&&J(t);return{c(){e=s("div"),a.c(),i=L(),$&&$.c(),c=d(),o(e,"class","fullPhoto svelte-12wz3c2")},m(t,s){l(t,e,s),w[n].m(e,null),l(t,i,s),$&&$.m(t,s),l(t,c,s),p=!0},p(t,s){let o=n;n=v(t),n===o?w[n].p(t,s):(f(),g(w[o],1,1,(()=>{w[o]=null})),m(),a=w[n],a?a.p(t,s):(a=w[n]=u[n](t),a.c()),h(a,1),a.m(e,null)),t[1]?$?($.p(t,s),2&s&&h($,1)):($=J(t),$.c(),h($,1),$.m(c.parentNode,c)):$&&(f(),g($,1,1,(()=>{$=null})),m())},i(t){p||(h(a),h($),p=!0)},o(t){g(a),g($),p=!1},d(t){t&&r(e),w[n].d(),t&&r(i),$&&$.d(t),t&&r(c)}}}function H(t){let e;return{c(){e=C("Loading…")},m(t,n){l(t,e,n)},p:a,i:a,o:a,d(t){t&&r(e)}}}function V(t){let e,n;return e=new A({}),{c(){b(e.$$.fragment)},m(t,s){I(e,t,s),n=!0},p:a,i(t){n||(h(e.$$.fragment,t),n=!0)},o(t){g(e.$$.fragment,t),n=!1},d(t){N(e,t)}}}function E(t){let e,n;return e=new F({props:{photo:t[0],lazy:!1,size:X,objectFit:"contain"}}),{c(){b(e.$$.fragment)},m(t,s){I(e,t,s),n=!0},p(t,n){const s={};1&n&&(s.photo=t[0]),e.$set(s)},i(t){n||(h(e.$$.fragment,t),n=!0)},o(t){g(e.$$.fragment,t),n=!1},d(t){N(e,t)}}}function J(t){let e,n,a,i,c,p,u,d,w,v,$,k,y,z,x,T,F,P,_,D,A,G,H,V,E,J,Q,W,X,Y,Z,tt,et,nt,st,ot,lt=t[0].title+"",at=t[0].width+"",rt=t[0].height+"",it=S(t[0].size)+"",ct=t[0].uploadTimeStamp+"",pt=(t[0].tags.length>0?t[0].tags.join(", "):"(None)")+"";E=new O({props:{margin:"0 10px 0 0",$$slots:{default:[K]},$$scope:{ctx:t}}});let ut=t[0].gpsLat&&t[0].gpsLon&&U(t),dt=t[2],ft=[];for(let e=0;e<dt.length;e+=1)ft[e]=R(B(t,dt,e));return{c(){e=s("div"),n=s("div"),a=s("span"),a.textContent="Title:",i=L(),c=C(lt),p=L(),u=s("table"),d=s("tr"),w=s("td"),w.textContent="Original: ",v=s("td"),$=C(at),k=C("×"),y=C(rt),z=C(" ("),x=C(it),T=C(")"),F=L(),P=s("tr"),_=s("td"),_.textContent="Uploaded: ",D=s("td"),A=C(ct),G=L(),H=s("div"),V=s("a"),b(E.$$.fragment),Q=L(),ut&&ut.c(),W=L(),X=s("table"),Y=s("tr"),Z=s("td"),Z.textContent="Tags",tt=L(),et=s("td"),nt=C(pt),st=L();for(let t=0;t<ft.length;t+=1)ft[t].c();o(a,"class","label svelte-12wz3c2"),o(n,"class","title svelte-12wz3c2"),o(w,"class","label svelte-12wz3c2"),o(_,"class","label svelte-12wz3c2"),o(u,"class","photoMeta svelte-12wz3c2"),o(V,"href",J="/api/photo/orig/"+t[0].id),o(V,"tinro-ignore",""),o(V,"class","svelte-12wz3c2"),o(H,"class","dlOrig svelte-12wz3c2"),o(Z,"class","label svelte-12wz3c2"),o(et,"class","value"),o(X,"class","photoMeta svelte-12wz3c2"),o(e,"class","metadata svelte-12wz3c2")},m(t,s){l(t,e,s),j(e,n),j(n,a),j(n,i),j(n,c),j(e,p),j(e,u),j(u,d),j(d,w),j(d,v),j(v,$),j(v,k),j(v,y),j(v,z),j(v,x),j(v,T),j(u,F),j(u,P),j(P,_),j(P,D),j(D,A),j(e,G),j(e,H),j(H,V),I(E,V,null),j(e,Q),ut&&ut.m(e,null),j(e,W),j(e,X),j(X,Y),j(Y,Z),j(Y,tt),j(Y,et),j(et,nt),j(X,st);for(let t=0;t<ft.length;t+=1)ft[t].m(X,null);ot=!0},p(t,n){(!ot||1&n)&&lt!==(lt=t[0].title+"")&&M(c,lt),(!ot||1&n)&&at!==(at=t[0].width+"")&&M($,at),(!ot||1&n)&&rt!==(rt=t[0].height+"")&&M(y,rt),(!ot||1&n)&&it!==(it=S(t[0].size)+"")&&M(x,it),(!ot||1&n)&&ct!==(ct=t[0].uploadTimeStamp+"")&&M(A,ct);const s={};if(1024&n&&(s.$$scope={dirty:n,ctx:t}),E.$set(s),(!ot||1&n&&J!==(J="/api/photo/orig/"+t[0].id))&&o(V,"href",J),t[0].gpsLat&&t[0].gpsLon?ut?(ut.p(t,n),1&n&&h(ut,1)):(ut=U(t),ut.c(),h(ut,1),ut.m(e,W)):ut&&(f(),g(ut,1,1,(()=>{ut=null})),m()),(!ot||1&n)&&pt!==(pt=(t[0].tags.length>0?t[0].tags.join(", "):"(None)")+"")&&M(nt,pt),5&n){let e;for(dt=t[2],e=0;e<dt.length;e+=1){const s=B(t,dt,e);ft[e]?ft[e].p(s,n):(ft[e]=R(s),ft[e].c(),ft[e].m(X,null))}for(;e<ft.length;e+=1)ft[e].d(1);ft.length=dt.length}},i(t){ot||(h(E.$$.fragment,t),h(ut),ot=!0)},o(t){g(E.$$.fragment,t),g(ut),ot=!1},d(t){t&&r(e),N(E),ut&&ut.d(),q(ft,t)}}}function K(t){let e,n,a,i,c,p,u;return{c(){e=T("svg"),n=T("rect"),a=T("polyline"),i=T("line"),c=T("path"),p=L(),u=s("div"),u.textContent="Download Original",o(n,"width","256"),o(n,"height","256"),o(n,"fill","none"),o(a,"points","86 110 128 152 170 110"),o(a,"fill","none"),o(a,"stroke","currentColor"),o(a,"stroke-linecap","round"),o(a,"stroke-linejoin","round"),o(a,"stroke-width","24"),o(i,"x1","128"),o(i,"y1","39.97056"),o(i,"x2","128"),o(i,"y2","151.97056"),o(i,"fill","none"),o(i,"stroke","currentColor"),o(i,"stroke-linecap","round"),o(i,"stroke-linejoin","round"),o(i,"stroke-width","24"),o(c,"d","M224,136v72a8,8,0,0,1-8,8H40a8,8,0,0,1-8-8V136"),o(c,"fill","none"),o(c,"stroke","currentColor"),o(c,"stroke-linecap","round"),o(c,"stroke-linejoin","round"),o(c,"stroke-width","24"),o(e,"xmlns","http://www.w3.org/2000/svg"),o(e,"fill","currentColor"),o(e,"viewBox","0 0 256 256"),o(e,"class","svelte-12wz3c2")},m(t,s){l(t,e,s),j(e,n),j(e,a),j(e,i),j(e,c),l(t,p,s),l(t,u,s)},d(t){t&&r(e),t&&r(p),t&&r(u)}}}function U(t){let e,n,a,i,c,p,u,d,f,m,w,v,$,k,y,z;return n=new P({props:{photos:[t[0]],exploreIconOnly:!0,popups:!1}}),{c(){e=s("div"),b(n.$$.fragment),a=L(),i=s("div"),c=C("{"),p=L(),u=s("a"),d=C("OpenStreetMap"),m=C("\n                    | "),w=s("a"),v=C("Google Maps"),k=L(),y=C("}"),o(e,"class","photoMap svelte-12wz3c2"),o(u,"target","_"),o(u,"href",f="https://www.openstreetmap.org/?mlat="+t[0].gpsLat+"&mlon="+t[0].gpsLon+"#map=18/"+t[0].gpsLat+"/"+t[0].gpsLon),o(u,"class","svelte-12wz3c2"),o(w,"target","_"),o(w,"href",$="https://www.google.com/maps/search/?api=1&query="+t[0].gpsLat+","+t[0].gpsLon),o(w,"class","svelte-12wz3c2"),o(i,"class","mapLinks svelte-12wz3c2")},m(t,s){l(t,e,s),I(n,e,null),l(t,a,s),l(t,i,s),j(i,c),j(i,p),j(i,u),j(u,d),j(i,m),j(i,w),j(w,v),j(i,k),j(i,y),z=!0},p(t,e){const s={};1&e&&(s.photos=[t[0]]),n.$set(s),(!z||1&e&&f!==(f="https://www.openstreetmap.org/?mlat="+t[0].gpsLat+"&mlon="+t[0].gpsLon+"#map=18/"+t[0].gpsLat+"/"+t[0].gpsLon))&&o(u,"href",f),(!z||1&e&&$!==($="https://www.google.com/maps/search/?api=1&query="+t[0].gpsLat+","+t[0].gpsLon))&&o(w,"href",$)},i(t){z||(h(n.$$.fragment,t),z=!0)},o(t){g(n.$$.fragment,t),z=!1},d(t){t&&r(e),N(n),t&&r(a),t&&r(i)}}}function Q(t){let e,n,a,i,c,p,u,d=t[7].value+"",f=(null==t[7].filter?t[0][t[7].key]:t[7].filter(t[0][t[7].key]))+"";return{c(){e=s("tr"),n=s("td"),a=C(d),i=L(),c=s("td"),p=C(f),u=L(),o(n,"class","label svelte-12wz3c2"),o(c,"class","value")},m(t,s){l(t,e,s),j(e,n),j(n,a),j(e,i),j(e,c),j(c,p),j(e,u)},p(t,e){1&e&&f!==(f=(null==t[7].filter?t[0][t[7].key]:t[7].filter(t[0][t[7].key]))+"")&&M(p,f)},d(t){t&&r(e)}}}function R(t){let e,n=null!=t[0][t[7].key]&&Q(t);return{c(){n&&n.c(),e=d()},m(t,s){n&&n.m(t,s),l(t,e,s)},p(t,s){null!=t[0][t[7].key]?n?n.p(t,s):(n=Q(t),n.c(),n.m(e.parentNode,e)):n&&(n.d(1),n=null)},d(t){n&&n.d(t),t&&r(e)}}}function W(t){let e,n,s,o;const a=[H,G],i=[];function c(t,e){return t[0]?1:0}return e=c(t),n=i[e]=a[e](t),{c(){n.c(),s=d()},m(t,n){i[e].m(t,n),l(t,s,n),o=!0},p(t,[o]){let l=e;e=c(t),e===l?i[e].p(t,o):(f(),g(i[l],1,1,(()=>{i[l]=null})),m(),n=i[e],n?n.p(t,o):(n=i[e]=a[e](t),n.c()),h(n,1),n.m(s.parentNode,s))},i(t){o||(h(n),o=!0)},o(t){g(n),o=!1},d(t){i[e].d(t),t&&r(s)}}}const X="large";function Y(t,e,n){let s,o,l;i(t,c,(t=>n(0,s=t))),i(t,w,(t=>n(4,o=t))),i(t,v,(t=>n(1,l=t)));var a=this&&this.__awaiter||function(t,e,n,s){return new(n||(n=Promise))((function(o,l){function a(t){try{i(s.next(t))}catch(t){l(t)}}function r(t){try{i(s.throw(t))}catch(t){l(t)}}function i(t){var e;t.done?o(t.value):(e=t.value,e instanceof n?e:new n((function(t){t(e)}))).then(a,r)}i((s=s.apply(t,e||[])).next())}))};const r=[{key:"creationDate",value:"Taken",filter:z},{key:"mime",value:"Format"},{key:"make",value:"Camera Make"},{key:"model",value:"Camera Model"},{key:"lens",value:"Lens"},{key:"aperture",value:"Aperture",filter:t=>`f/${t}`},{key:"iso",value:"ISO"},{key:"shutterSpeed",value:"Shutter Speed",filter:x}];let{photoIdentifier:p}=e;return $((()=>{k(c,s=null,s)})),t.$$set=t=>{"photoIdentifier"in t&&n(3,p=t.photoIdentifier)},t.$$.update=()=>{8&t.$$.dirty&&function(t){a(this,void 0,void 0,(function*(){const e=parseInt(t);if(isNaN(e))return void y.goto(`/album/${o.slug}`);const n=o.photos.findIndex((t=>t.id==e));-1!=n?k(c,s=o.photos[n],s):y.goto(`/album/${o.slug}`)}))}(p)},[s,l,r,p]}export default class extends t{constructor(t){super(),e(this,t,Y,W,n,{photoIdentifier:3})}}
