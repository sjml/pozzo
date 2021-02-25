import{S as e,i as t,s as n,H as s,g as l,k as i,n as o,w as r,j as a,h as c,p as u,v as d,A as f,b as p,B as m,C as h,o as v,U as g,z as $,c as w,m as x,t as y,a as k,d as U,D as b,E as C,F as L,G as j,r as S,I as T,e as B,y as D,J as F,K as P,L as z,M as _,u as N,x as A,N as I,P as E,Q as M,T as V,V as H,W as R}from"./main-7b06f501.js";function W(e){let t,n;return{c(){t=l("img"),t.src!==(n=e[2])&&i(t,"src",n),i(t,"alt",e[3]),i(t,"class","svelte-42cape")},m(e,n){o(e,t,n)},p(e,s){4&s&&t.src!==(n=e[2])&&i(t,"src",n),8&s&&i(t,"alt",e[3])},d(e){e&&r(t)}}}function Z(e){let t,n,p,m,h,v,g,$,w,x,y,k,U,b,C,L,j,S,T,B,D=e[0].file.name+"",F=s(e[0].file.size)+"",P=e[2]&&W(e);return{c(){t=l("div"),n=l("div"),p=l("div"),m=l("div"),h=l("span"),h.textContent="Name:",v=a(),g=c(D),$=a(),w=l("div"),x=l("span"),x.textContent="Size:",y=a(),k=c(F),U=a(),b=l("div"),P&&P.c(),C=a(),L=l("div"),j=l("progress"),S=a(),T=l("div"),B=c(e[5]),i(h,"class","label svelte-42cape"),i(m,"class","name"),i(x,"class","label svelte-42cape"),i(w,"class","size"),i(p,"class","meta svelte-42cape"),i(b,"class","preview svelte-42cape"),i(n,"class","info svelte-42cape"),i(j,"class","svelte-42cape"),i(T,"class","status svelte-42cape"),i(L,"class","status svelte-42cape"),i(t,"class","fileUploader svelte-42cape")},m(s,l){o(s,t,l),u(t,n),u(n,p),u(p,m),u(m,h),u(m,v),u(m,g),u(p,$),u(p,w),u(w,x),u(w,y),u(w,k),u(n,U),u(n,b),P&&P.m(b,null),u(t,C),u(t,L),u(L,j),e[7](j),u(L,S),u(L,T),u(T,B),e[8](t)},p(e,[t]){1&t&&D!==(D=e[0].file.name+"")&&d(g,D),1&t&&F!==(F=s(e[0].file.size)+"")&&d(k,F),e[2]?P?P.p(e,t):(P=W(e),P.c(),P.m(b,null)):P&&(P.d(1),P=null),32&t&&d(B,e[5])},i:f,o:f,d(n){n&&r(t),P&&P.d(),e[7](null),e[8](null)}}}function G(e,t,n){let s;p(e,m,(e=>n(9,s=e)));var l=this&&this.__awaiter||function(e,t,n,s){return new(n||(n=Promise))((function(l,i){function o(e){try{a(s.next(e))}catch(e){i(e)}}function r(e){try{a(s.throw(e))}catch(e){i(e)}}function a(e){var t;e.done?l(e.value):(t=e.value,t instanceof n?t:new n((function(e){e(t)}))).then(o,r)}a((s=s.apply(e,t||[])).next())}))};const i=h();let o,r,{uploadStatus:a}=t,c=null,u="";v((()=>{if(n(0,a.startUploadCallback=()=>f(),a),n(1,o.value=0,o),a.file.type.startsWith("image")){const e=new FileReader;e.readAsDataURL(a.file),e.onloadend=()=>{n(2,c=e.result),n(3,u=a.file.name)}}}));let d="Pending…";function f(){return l(this,void 0,void 0,(function*(){if(a.status>0)return;n(0,a.status=1,a),n(5,d="Uploading…"),s||r.scrollIntoView({behavior:"smooth",block:"center"});const e=yield g(a,(e=>n(1,o.value=e,o)),(e=>{n(5,d="Processing…"),o.removeAttribute("value")}));n(1,o.value=1,o),e.success?(n(5,d="Done!"),n(0,a.status=2,a),i("fileUploadDone")):(n(5,d="Failed :("),n(0,a.status=3,a),i("fileUploadFailed"))}))}return e.$$set=e=>{"uploadStatus"in e&&n(0,a=e.uploadStatus)},e.$$.update=()=>{3&e.$$.dirty&&o&&0==a.status&&n(1,o.value=0,o)},[a,o,c,u,r,d,f,function(e){$[e?"unshift":"push"]((()=>{o=e,n(1,o),n(0,a)}))},function(e){$[e?"unshift":"push"]((()=>{r=e,n(4,r)}))}]}class J extends e{constructor(e){super(),t(this,e,G,Z,n,{uploadStatus:0,startUpload:6})}get startUpload(){return this.$$.ctx[6]}}function K(e,t,n){const s=e.slice();return s[18]=t[n],s}function O(e){let t,n,s,c,d,f,p;function m(e,t){return e[2]>0?q:Q}let h=m(e),v=h(e);return c=new P({props:{title:"Upload",isBig:!0,margin:"0 30px",$$slots:{default:[X]},$$scope:{ctx:e}}}),c.$on("click",e[8]),f=new P({props:{title:"Cancel",isBig:!0,margin:"0 30px",$$slots:{default:[Y]},$$scope:{ctx:e}}}),f.$on("click",e[9]),{c(){t=l("div"),v.c(),n=a(),s=l("div"),w(c.$$.fragment),d=a(),w(f.$$.fragment),i(s,"class","yesno svelte-hxbnie"),i(t,"class","confirm svelte-hxbnie")},m(e,l){o(e,t,l),v.m(t,null),u(t,n),u(t,s),x(c,s,null),u(s,d),x(f,s,null),p=!0},p(e,s){h===(h=m(e))&&v?v.p(e,s):(v.d(1),v=h(e),v&&(v.c(),v.m(t,n)));const l={};2097152&s&&(l.$$scope={dirty:s,ctx:e}),c.$set(l);const i={};2097152&s&&(i.$$scope={dirty:s,ctx:e}),f.$set(i)},i(e){p||(y(c.$$.fragment,e),y(f.$$.fragment,e),p=!0)},o(e){k(c.$$.fragment,e),k(f.$$.fragment,e),p=!1},d(e){e&&r(t),v.d(),U(c),U(f)}}}function Q(e){let t,n,s,l,i,a=e[0].length+"",u=1==e[0].length?"":"s";return{c(){t=c("Upload "),n=c(a),s=c(" image"),l=c(u),i=c("?")},m(e,r){o(e,t,r),o(e,n,r),o(e,s,r),o(e,l,r),o(e,i,r)},p(e,t){1&t&&a!==(a=e[0].length+"")&&d(n,a),1&t&&u!==(u=1==e[0].length?"":"s")&&d(l,u)},d(e){e&&r(t),e&&r(n),e&&r(s),e&&r(l),e&&r(i)}}}function q(e){let t,n,s=1==e[2]?"This image ":"These images ";return{c(){t=c(s),n=c("did not upload\n                successfully. Do you want to try again?")},m(e,s){o(e,t,s),o(e,n,s)},p(e,n){4&n&&s!==(s=1==e[2]?"This image ":"These images ")&&d(t,s)},d(e){e&&r(t),e&&r(n)}}}function X(e){let t,n,s,l,a,d;return{c(){t=z("svg"),n=z("rect"),s=z("circle"),l=z("polyline"),a=z("line"),d=c("\n                    Upload"),i(n,"width","256"),i(n,"height","256"),i(n,"fill","none"),i(s,"cx","128"),i(s,"cy","128"),i(s,"r","96"),i(s,"fill","none"),i(s,"stroke","currentColor"),i(s,"stroke-linecap","round"),i(s,"stroke-linejoin","round"),i(s,"stroke-width","24"),i(l,"points","94.059 121.941 128 88 161.941 121.941"),i(l,"fill","none"),i(l,"stroke","currentColor"),i(l,"stroke-linecap","round"),i(l,"stroke-linejoin","round"),i(l,"stroke-width","24"),i(a,"x1","128"),i(a,"y1","168"),i(a,"x2","128"),i(a,"y2","88"),i(a,"fill","none"),i(a,"stroke","currentColor"),i(a,"stroke-linecap","round"),i(a,"stroke-linejoin","round"),i(a,"stroke-width","24"),i(t,"xmlns","http://www.w3.org/2000/svg"),i(t,"fill","currentColor"),i(t,"viewBox","0 0 256 256")},m(e,i){o(e,t,i),u(t,n),u(t,s),u(t,l),u(t,a),o(e,d,i)},d(e){e&&r(t),e&&r(d)}}}function Y(e){let t,n,s,l,a,d;return{c(){t=z("svg"),n=z("rect"),s=z("circle"),l=z("line"),a=z("line"),d=c("\n                    Cancel"),i(n,"width","256"),i(n,"height","256"),i(n,"fill","none"),i(s,"cx","128"),i(s,"cy","128"),i(s,"r","96"),i(s,"fill","none"),i(s,"stroke","currentColor"),i(s,"stroke-linecap","round"),i(s,"stroke-linejoin","round"),i(s,"stroke-width","24"),i(l,"x1","160"),i(l,"y1","96"),i(l,"x2","96"),i(l,"y2","160"),i(l,"fill","none"),i(l,"stroke","currentColor"),i(l,"stroke-linecap","round"),i(l,"stroke-linejoin","round"),i(l,"stroke-width","24"),i(a,"x1","160"),i(a,"y1","160"),i(a,"x2","96"),i(a,"y2","96"),i(a,"fill","none"),i(a,"stroke","currentColor"),i(a,"stroke-linecap","round"),i(a,"stroke-linejoin","round"),i(a,"stroke-width","24"),i(t,"xmlns","http://www.w3.org/2000/svg"),i(t,"fill","currentColor"),i(t,"viewBox","0 0 256 256")},m(e,i){o(e,t,i),u(t,n),u(t,s),u(t,l),u(t,a),o(e,d,i)},d(e){e&&r(t),e&&r(d)}}}function ee(e){let t,n,s=e[1],l=[];for(let t=0;t<s.length;t+=1)l[t]=te(K(e,s,t));const i=e=>k(l[e],1,1,(()=>{l[e]=null}));return{c(){for(let e=0;e<l.length;e+=1)l[e].c();t=b()},m(e,s){for(let t=0;t<l.length;t+=1)l[t].m(e,s);o(e,t,s),n=!0},p(e,n){if(194&n){let o;for(s=e[1],o=0;o<s.length;o+=1){const i=K(e,s,o);l[o]?(l[o].p(i,n),y(l[o],1)):(l[o]=te(i),l[o].c(),y(l[o],1),l[o].m(t.parentNode,t))}for(C(),o=s.length;o<l.length;o+=1)i(o);L()}},i(e){if(!n){for(let e=0;e<s.length;e+=1)y(l[e]);n=!0}},o(e){l=l.filter(Boolean);for(let e=0;e<l.length;e+=1)k(l[e]);n=!1},d(e){j(l,e),e&&r(t)}}}function te(e){let t,n;return t=new J({props:{uploadStatus:e[18]}}),t.$on("fileUploadDone",e[6]),t.$on("fileUploadFailed",e[7]),{c(){w(t.$$.fragment)},m(e,s){x(t,e,s),n=!0},p(e,n){const s={};2&n&&(s.uploadStatus=e[18]),t.$set(s)},i(e){n||(y(t.$$.fragment,e),n=!0)},o(e){k(t.$$.fragment,e),n=!1},d(e){U(t,e)}}}function ne(e){let t,n,s,c,d,f=e[3]&&O(e),p=e[1]&&ee(e);return{c(){t=l("div"),f&&f.c(),n=a(),p&&p.c(),i(t,"class","fileUploadList svelte-hxbnie")},m(l,i){o(l,t,i),f&&f.m(t,null),u(t,n),p&&p.m(t,null),s=!0,c||(d=S(t,"click",e[10]),c=!0)},p(e,[s]){e[3]?f?(f.p(e,s),8&s&&y(f,1)):(f=O(e),f.c(),y(f,1),f.m(t,n)):f&&(C(),k(f,1,1,(()=>{f=null})),L()),e[1]?p?(p.p(e,s),2&s&&y(p,1)):(p=ee(e),p.c(),y(p,1),p.m(t,null)):p&&(C(),k(p,1,1,(()=>{p=null})),L())},i(e){s||(y(f),y(p),s=!0)},o(e){k(f),k(p),s=!1},d(e){e&&r(t),f&&f.d(),p&&p.d(),c=!1,d()}}}function se(e){return e.preventDefault(),e.returnValue="","..."}function le(e,t,n){let s,l,i;p(e,m,(e=>n(4,s=e))),p(e,T,(e=>n(13,l=e))),p(e,B,(e=>n(14,i=e)));var o=this&&this.__awaiter||function(e,t,n,s){return new(n||(n=Promise))((function(l,i){function o(e){try{a(s.next(e))}catch(e){i(e)}}function r(e){try{a(s.throw(e))}catch(e){i(e)}}function a(e){var t;e.done?l(e.value):(t=e.value,t instanceof n?t:new n((function(e){e(t)}))).then(o,r)}a((s=s.apply(e,t||[])).next())}))};const r=h();let a,{fileList:c}=t;v((()=>{D(m,s=!1,s);let e=null,t=1;if(null!=l){e=l.id;const n=l.photos.map((e=>e.ordering));t=0==n.length?1:Math.max(...n)+1}n(1,a=c.map(((n,s)=>({file:n,status:0,index:s+t,targetAlbum:e}))))})),F((()=>{window.removeEventListener("beforeunload",se)}));let u=0,d=0;function f(){for(;d<i.simultaneousUploads&&!(u>=a.length);)a[u].startUploadCallback(),u+=1,d+=1}function g(){d-=1,0==d&&u>=a.length?function(){if(0==$)return void r("finished");n(1,a=a.filter((e=>3==e.status))),n(3,w=!0)}():f()}let $=0;let w=!0;return e.$$set=e=>{"fileList"in e&&n(0,c=e.fileList)},[c,a,$,w,s,r,g,function(){n(2,$+=1),g()},function(){return o(this,void 0,void 0,(function*(){window.addEventListener("beforeunload",se),n(2,$=0),u=0,d=0,n(3,w=!1);for(let e=0;e<a.length;e++)n(1,a[e].status=0,a);yield _(),yield new Promise((e=>setTimeout(e,400))),D(m,s=!1,s),f()}))},()=>r("dismissed"),()=>D(m,s=!0,s)]}class ie extends e{constructor(e){super(),t(this,e,le,ne,n,{fileList:0})}}function oe(e){let t,n,s,l;const i=[ae,re],a=[];function c(e,t){return e[0]?0:1}return t=c(e),n=a[t]=i[t](e),{c(){n.c(),s=b()},m(e,n){a[t].m(e,n),o(e,s,n),l=!0},p(e,l){let o=t;t=c(e),t===o?a[t].p(e,l):(C(),k(a[o],1,1,(()=>{a[o]=null})),L(),n=a[t],n?n.p(e,l):(n=a[t]=i[t](e),n.c()),y(n,1),n.m(s.parentNode,s))},i(e){l||(y(n),l=!0)},o(e){k(n),l=!1},d(e){a[t].d(e),e&&r(s)}}}function re(e){let t,n,s,a,c;return{c(){t=l("div"),n=l("div"),n.innerHTML='<div class="dragIndicatorOutline svelte-i5ms3h"></div>',i(n,"class","dragIndicator svelte-i5ms3h"),i(t,"class","uploadZone svelte-i5ms3h")},m(e,s){o(e,t,s),u(t,n),c=!0},p:f,i(e){c||(E((()=>{a&&a.end(1),s||(s=M(n,R,{duration:100})),s.start()})),c=!0)},o(e){s&&s.invalidate(),a=V(n,R,{duration:200}),c=!1},d(e){e&&r(t),e&&a&&a.end()}}}function ae(e){let t,n,s;return n=new ie({props:{fileList:e[0]}}),n.$on("finished",e[4]),n.$on("dismissed",e[5]),{c(){t=l("div"),w(n.$$.fragment),i(t,"class","uploadZone svelte-i5ms3h")},m(e,l){o(e,t,l),x(n,t,null),s=!0},p(e,t){const s={};1&t&&(s.fileList=e[0]),n.$set(s)},i(e){s||(y(n.$$.fragment,e),s=!0)},o(e){k(n.$$.fragment,e),s=!1},d(e){e&&r(t),U(n)}}}function ce(e){let t,n,s,l,i=(e[1]>0||null!=e[0])&&e[2]&&oe(e);return{c(){i&&i.c(),t=b()},m(r,a){i&&i.m(r,a),o(r,t,a),n=!0,s||(l=[S(window,"dragover",N(e[6])),S(window,"dragenter",N(e[7])),S(window,"dragleave",N(e[8])),S(window,"drop",N(e[3]))],s=!0)},p(e,[n]){(e[1]>0||null!=e[0])&&e[2]?i?(i.p(e,n),7&n&&y(i,1)):(i=oe(e),i.c(),y(i,1),i.m(t.parentNode,t)):i&&(C(),k(i,1,1,(()=>{i=null})),L())},i(e){n||(y(i),n=!0)},o(e){k(i),n=!1},d(e){i&&i.d(e),e&&r(t),s=!1,A(l)}}}function ue(e,t,n){let s,l;p(e,B,(e=>n(9,s=e))),p(e,I,(e=>n(2,l=e)));var i=this&&this.__awaiter||function(e,t,n,s){return new(n||(n=Promise))((function(l,i){function o(e){try{a(s.next(e))}catch(e){i(e)}}function r(e){try{a(s.throw(e))}catch(e){i(e)}}function a(e){var t;e.done?l(e.value):(t=e.value,t instanceof n?t:new n((function(e){e(t)}))).then(o,r)}a((s=s.apply(e,t||[])).next())}))};let o;const r=h();let a=0;function c(e){return!!s.formats.includes(e.type)&&!(e.size>s.maxUploadBytes)}return[o,a,l,function(e){return i(this,void 0,void 0,(function*(){n(1,a=0);let t=[...e.dataTransfer.files];t=t.filter(c),0!=t.length&&n(0,o=t)}))},function(){r("done",{numFiles:o.length}),n(0,o=null)},function(){n(0,o=null),r("done",{numFiles:0})},function(t){H(e,t)},()=>n(1,++a),()=>n(1,--a)]}export default class extends e{constructor(e){super(),t(this,e,ue,ce,n,{})}}
