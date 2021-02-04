var app=function(){"use strict";function t(){}const e=t=>t;function n(t,e){for(const n in e)t[n]=e[n];return t}function o(t){return t()}function i(){return Object.create(null)}function r(t){t.forEach(o)}function s(t){return"function"==typeof t}function c(t,e){return t!=t?e==e:t!==e||t&&"object"==typeof t||"function"==typeof t}function a(e,...n){if(null==e)return t;const o=e.subscribe(...n);return o.unsubscribe?()=>o.unsubscribe():o}function u(t,e,n){t.$$.on_destroy.push(a(e,n))}function l(t,e,n,o){if(t){const i=h(t,e,n,o);return t[0](i)}}function h(t,e,o,i){return t[1]&&i?n(o.ctx.slice(),t[1](i(e))):o.ctx}function d(t,e,n,o,i,r,s){const c=function(t,e,n,o){if(t[2]&&o){const i=t[2](o(n));if(void 0===e.dirty)return i;if("object"==typeof i){const t=[],n=Math.max(e.dirty.length,i.length);for(let o=0;o<n;o+=1)t[o]=e.dirty[o]|i[o];return t}return e.dirty|i}return e.dirty}(e,o,i,r);if(c){const i=h(e,n,o,s);t.p(i,c)}}function f(t){const e={};for(const n in t)"$"!==n[0]&&(e[n]=t[n]);return e}const p="undefined"!=typeof window;let g=p?()=>window.performance.now():()=>Date.now(),m=p?t=>requestAnimationFrame(t):t;const $=new Set;function w(t){$.forEach((e=>{e.c(t)||($.delete(e),e.f())})),0!==$.size&&m(w)}function y(t,e){t.appendChild(e)}function x(t,e,n){t.insertBefore(e,n||null)}function b(t){t.parentNode.removeChild(t)}function _(t){return document.createElement(t)}function v(t){return document.createTextNode(t)}function R(){return v(" ")}function H(){return v("")}function k(t,e,n,o){return t.addEventListener(e,n,o),()=>t.removeEventListener(e,n,o)}function P(t,e,n){null==n?t.removeAttribute(e):t.getAttribute(e)!==n&&t.setAttribute(e,n)}let C;function S(){if(void 0===C){C=!1;try{"undefined"!=typeof window&&window.parent&&window.parent.document}catch(t){C=!0}}return C}const j=new Set;let N,E=0;function L(t,e){const n=(t.style.animation||"").split(", "),o=n.filter(e?t=>t.indexOf(e)<0:t=>-1===t.indexOf("__svelte")),i=n.length-o.length;i&&(t.style.animation=o.join(", "),E-=i,E||m((()=>{E||(j.forEach((t=>{const e=t.__svelte_stylesheet;let n=e.cssRules.length;for(;n--;)e.deleteRule(n);t.__svelte_rules={}})),j.clear())})))}function I(t){N=t}function z(){if(!N)throw new Error("Function called outside component initialization");return N}function O(t){z().$$.on_mount.push(t)}function A(t,e){z().$$.context.set(t,e)}function M(t){return z().$$.context.get(t)}const T=[],B=[],W=[],F=[],D=Promise.resolve();let J=!1;function U(t){W.push(t)}let q=!1;const G=new Set;function V(){if(!q){q=!0;do{for(let t=0;t<T.length;t+=1){const e=T[t];I(e),Y(e.$$)}for(I(null),T.length=0;B.length;)B.pop()();for(let t=0;t<W.length;t+=1){const e=W[t];G.has(e)||(G.add(e),e())}W.length=0}while(T.length);for(;F.length;)F.pop()();J=!1,q=!1,G.clear()}}function Y(t){if(null!==t.fragment){t.update(),r(t.before_update);const e=t.dirty;t.dirty=[-1],t.fragment&&t.fragment.p(t.ctx,e),t.after_update.forEach(U)}}let K;function Q(t,e,n){t.dispatchEvent(function(t,e){const n=document.createEvent("CustomEvent");return n.initCustomEvent(t,!1,!1,e),n}(`${e?"intro":"outro"}${n}`))}const X=new Set;let Z;function tt(){Z={r:0,c:[],p:Z}}function et(){Z.r||r(Z.c),Z=Z.p}function nt(t,e){t&&t.i&&(X.delete(t),t.i(e))}function ot(t,e,n,o){if(t&&t.o){if(X.has(t))return;X.add(t),Z.c.push((()=>{X.delete(t),o&&(n&&t.d(1),o())})),t.o(e)}}const it={duration:0};function rt(n,o,i){let c,a=o(n,i),u=!0;const l=Z;function h(){const{delay:o=0,duration:i=300,easing:s=e,tick:h=t,css:d}=a||it;d&&(c=function(t,e,n,o,i,r,s,c=0){const a=16.666/o;let u="{\n";for(let t=0;t<=1;t+=a){const o=e+(n-e)*r(t);u+=100*t+`%{${s(o,1-o)}}\n`}const l=u+`100% {${s(n,1-n)}}\n}`,h=`__svelte_${function(t){let e=5381,n=t.length;for(;n--;)e=(e<<5)-e^t.charCodeAt(n);return e>>>0}(l)}_${c}`,d=t.ownerDocument;j.add(d);const f=d.__svelte_stylesheet||(d.__svelte_stylesheet=d.head.appendChild(_("style")).sheet),p=d.__svelte_rules||(d.__svelte_rules={});p[h]||(p[h]=!0,f.insertRule(`@keyframes ${h} ${l}`,f.cssRules.length));const g=t.style.animation||"";return t.style.animation=`${g?`${g}, `:""}${h} ${o}ms linear ${i}ms 1 both`,E+=1,h}(n,1,0,i,o,s,d));const f=g()+o,p=f+i;U((()=>Q(n,!1,"start"))),function(t){let e;0===$.size&&m(w),new Promise((n=>{$.add(e={c:t,f:n})}))}((t=>{if(u){if(t>=p)return h(0,1),Q(n,!1,"end"),--l.r||r(l.c),!1;if(t>=f){const e=s((t-f)/i);h(1-e,e)}}return u}))}return l.r+=1,s(a)?(K||(K=Promise.resolve(),K.then((()=>{K=null}))),K).then((()=>{a=a(),h()})):h(),{end(t){t&&a.tick&&a.tick(1,0),u&&(c&&L(n,c),u=!1)}}}function st(t){return"object"==typeof t&&null!==t?t:{}}function ct(t){t&&t.c()}function at(t,e,n){const{fragment:i,on_mount:c,on_destroy:a,after_update:u}=t.$$;i&&i.m(e,n),U((()=>{const e=c.map(o).filter(s);a?a.push(...e):r(e),t.$$.on_mount=[]})),u.forEach(U)}function ut(t,e){const n=t.$$;null!==n.fragment&&(r(n.on_destroy),n.fragment&&n.fragment.d(e),n.on_destroy=n.fragment=null,n.ctx=[])}function lt(t,e){-1===t.$$.dirty[0]&&(T.push(t),J||(J=!0,D.then(V)),t.$$.dirty.fill(0)),t.$$.dirty[e/31|0]|=1<<e%31}function ht(e,n,o,s,c,a,u=[-1]){const l=N;I(e);const h=e.$$={fragment:null,ctx:null,props:a,update:t,not_equal:c,bound:i(),on_mount:[],on_destroy:[],before_update:[],after_update:[],context:new Map(l?l.$$.context:[]),callbacks:i(),dirty:u,skip_bound:!1};let d=!1;if(h.ctx=o?o(e,n.props||{},((t,n,...o)=>{const i=o.length?o[0]:n;return h.ctx&&c(h.ctx[t],h.ctx[t]=i)&&(!h.skip_bound&&h.bound[t]&&h.bound[t](i),d&&lt(e,t)),n})):[],h.update(),d=!0,r(h.before_update),h.fragment=!!s&&s(h.ctx),n.target){if(n.hydrate){const t=function(t){return Array.from(t.childNodes)}(n.target);h.fragment&&h.fragment.l(t),t.forEach(b)}else h.fragment&&h.fragment.c();n.intro&&nt(e.$$.fragment),at(e,n.target,n.anchor),V()}I(l)}class dt{$destroy(){ut(this,1),this.$destroy=t}$on(t,e){const n=this.$$.callbacks[t]||(this.$$.callbacks[t]=[]);return n.push(e),()=>{const t=n.indexOf(e);-1!==t&&n.splice(t,1)}}$set(t){var e;this.$$set&&(e=t,0!==Object.keys(e).length)&&(this.$$.skip_bound=!0,this.$$set(t),this.$$.skip_bound=!1)}}const ft=[];function pt(e,n=t){let o;const i=[];function r(t){if(c(e,t)&&(e=t,o)){const t=!ft.length;for(let t=0;t<i.length;t+=1){const n=i[t];n[1](),ft.push(n,e)}if(t){for(let t=0;t<ft.length;t+=2)ft[t][0](ft[t+1]);ft.length=0}}}return{set:r,update:function(t){r(t(e))},subscribe:function(s,c=t){const a=[s,c];return i.push(a),1===i.length&&(o=n(r)||t),s(e),()=>{const t=i.indexOf(a);-1!==t&&i.splice(t,1),0===i.length&&(o(),o=null)}}}}function gt(e,n,o){const i=!Array.isArray(e),c=i?[e]:e,u=n.length<2;return{subscribe:pt(o,(e=>{let o=!1;const l=[];let h=0,d=t;const f=()=>{if(h)return;d();const o=n(i?l[0]:l,e);u?e(o):d=s(o)?o:t},p=c.map(((t,e)=>a(t,(t=>{l[e]=t,h&=~(1<<e),o&&f()}),(()=>{h|=1<<e}))));return o=!0,f(),function(){r(p),d()}})).subscribe}}const mt={},$t={};function wt(t){return{...t.location,state:t.history.state,key:t.history.state&&t.history.state.key||"initial"}}const yt=function(t,e){const n=[];let o=wt(t);return{get location(){return o},listen(e){n.push(e);const i=()=>{o=wt(t),e({location:o,action:"POP"})};return t.addEventListener("popstate",i),()=>{t.removeEventListener("popstate",i);const o=n.indexOf(e);n.splice(o,1)}},navigate(e,{state:i,replace:r=!1}={}){i={...i,key:Date.now()+""};try{r?t.history.replaceState(i,null,e):t.history.pushState(i,null,e)}catch(n){t.location[r?"replace":"assign"](e)}o=wt(t),n.forEach((t=>t({location:o,action:"PUSH"})))}}}(Boolean("undefined"!=typeof window&&window.document&&window.document.createElement)?window:function(t="/"){let e=0;const n=[{pathname:t,search:""}],o=[];return{get location(){return n[e]},addEventListener(t,e){},removeEventListener(t,e){},history:{get entries(){return n},get index(){return e},get state(){return o[e]},pushState(t,i,r){const[s,c=""]=r.split("?");e++,n.push({pathname:s,search:c}),o.push(t)},replaceState(t,i,r){const[s,c=""]=r.split("?");n[e]={pathname:s,search:c},o[e]=t}}}}()),xt=/^:(.+)/;function bt(t){return"*"===t[0]}function _t(t){return t.replace(/(^\/+|\/+$)/g,"").split("/")}function vt(t){return t.replace(/(^\/+|\/+$)/g,"")}function Rt(t,e){return{route:t,score:t.default?0:_t(t.path).reduce(((t,e)=>(t+=4,!function(t){return""===t}(e)?!function(t){return xt.test(t)}(e)?bt(e)?t-=5:t+=3:t+=2:t+=1,t)),0),index:e}}function Ht(t,e){let n,o;const[i]=e.split("?"),r=_t(i),s=""===r[0],c=function(t){return t.map(Rt).sort(((t,e)=>t.score<e.score?1:t.score>e.score?-1:t.index-e.index))}(t);for(let t=0,i=c.length;t<i;t++){const i=c[t].route;let a=!1;if(i.default){o={route:i,params:{},uri:e};continue}const u=_t(i.path),l={},h=Math.max(r.length,u.length);let d=0;for(;d<h;d++){const t=u[d],e=r[d];if(void 0!==t&&bt(t)){l["*"===t?"*":t.slice(1)]=r.slice(d).map(decodeURIComponent).join("/");break}if(void 0===e){a=!0;break}let n=xt.exec(t);if(n&&!s){const t=decodeURIComponent(e);l[n[1]]=t}else if(t!==e){a=!0;break}}if(!a){n={route:i,params:l,uri:"/"+r.slice(0,d).join("/")};break}}return n||o||null}function kt(t,e){return`${vt("/"===e?t:`${vt(t)}/${vt(e)}`)}/`}function Pt(t){let e;const n=t[9].default,o=l(n,t,t[8],null);return{c(){o&&o.c()},m(t,n){o&&o.m(t,n),e=!0},p(t,[e]){o&&o.p&&256&e&&d(o,n,t,t[8],e,null,null)},i(t){e||(nt(o,t),e=!0)},o(t){ot(o,t),e=!1},d(t){o&&o.d(t)}}}function Ct(t,e,n){let o,i,r,{$$slots:s={},$$scope:c}=e,{basepath:a="/"}=e,{url:l=null}=e;const h=M(mt),d=M($t),f=pt([]);u(t,f,(t=>n(7,r=t)));const p=pt(null);let g=!1;const m=h||pt(l?{pathname:l}:yt.location);u(t,m,(t=>n(6,i=t)));const $=d?d.routerBase:pt({path:a,uri:a});u(t,$,(t=>n(5,o=t)));const w=gt([$,p],(([t,e])=>{if(null===e)return t;const{path:n}=t,{route:o,uri:i}=e;return{path:o.default?n:o.path.replace(/\*.*$/,""),uri:i}}));return h||(O((()=>yt.listen((t=>{m.set(t.location)})))),A(mt,m)),A($t,{activeRoute:p,base:$,routerBase:w,registerRoute:function(t){const{path:e}=o;let{path:n}=t;if(t._path=n,t.path=kt(e,n),"undefined"==typeof window){if(g)return;const e=function(t,e){return Ht([t],e)}(t,i.pathname);e&&(p.set(e),g=!0)}else f.update((e=>(e.push(t),e)))},unregisterRoute:function(t){f.update((e=>{const n=e.indexOf(t);return e.splice(n,1),e}))}}),t.$$set=t=>{"basepath"in t&&n(3,a=t.basepath),"url"in t&&n(4,l=t.url),"$$scope"in t&&n(8,c=t.$$scope)},t.$$.update=()=>{if(32&t.$$.dirty){const{path:t}=o;f.update((e=>(e.forEach((e=>e.path=kt(t,e._path))),e)))}if(192&t.$$.dirty){const t=Ht(r,i.pathname);p.set(t)}},[f,m,$,a,l,o,i,r,c,s]}class St extends dt{constructor(t){super(),ht(this,t,Ct,Pt,c,{basepath:3,url:4})}}const jt=t=>({params:4&t,location:16&t}),Nt=t=>({params:t[2],location:t[4]});function Et(t){let e,n,o,i;const r=[It,Lt],s=[];function c(t,e){return null!==t[0]?0:1}return e=c(t),n=s[e]=r[e](t),{c(){n.c(),o=H()},m(t,n){s[e].m(t,n),x(t,o,n),i=!0},p(t,i){let a=e;e=c(t),e===a?s[e].p(t,i):(tt(),ot(s[a],1,1,(()=>{s[a]=null})),et(),n=s[e],n?n.p(t,i):(n=s[e]=r[e](t),n.c()),nt(n,1),n.m(o.parentNode,o))},i(t){i||(nt(n),i=!0)},o(t){ot(n),i=!1},d(t){s[e].d(t),t&&b(o)}}}function Lt(t){let e;const n=t[10].default,o=l(n,t,t[9],Nt);return{c(){o&&o.c()},m(t,n){o&&o.m(t,n),e=!0},p(t,e){o&&o.p&&532&e&&d(o,n,t,t[9],e,jt,Nt)},i(t){e||(nt(o,t),e=!0)},o(t){ot(o,t),e=!1},d(t){o&&o.d(t)}}}function It(t){let e,o,i;const r=[{location:t[4]},t[2],t[3]];var s=t[0];function c(t){let e={};for(let t=0;t<r.length;t+=1)e=n(e,r[t]);return{props:e}}return s&&(e=new s(c())),{c(){e&&ct(e.$$.fragment),o=H()},m(t,n){e&&at(e,t,n),x(t,o,n),i=!0},p(t,n){const i=28&n?function(t,e){const n={},o={},i={$$scope:1};let r=t.length;for(;r--;){const s=t[r],c=e[r];if(c){for(const t in s)t in c||(o[t]=1);for(const t in c)i[t]||(n[t]=c[t],i[t]=1);t[r]=c}else for(const t in s)i[t]=1}for(const t in o)t in n||(n[t]=void 0);return n}(r,[16&n&&{location:t[4]},4&n&&st(t[2]),8&n&&st(t[3])]):{};if(s!==(s=t[0])){if(e){tt();const t=e;ot(t.$$.fragment,1,0,(()=>{ut(t,1)})),et()}s?(e=new s(c()),ct(e.$$.fragment),nt(e.$$.fragment,1),at(e,o.parentNode,o)):e=null}else s&&e.$set(i)},i(t){i||(e&&nt(e.$$.fragment,t),i=!0)},o(t){e&&ot(e.$$.fragment,t),i=!1},d(t){t&&b(o),e&&ut(e,t)}}}function zt(t){let e,n,o=null!==t[1]&&t[1].route===t[7]&&Et(t);return{c(){o&&o.c(),e=H()},m(t,i){o&&o.m(t,i),x(t,e,i),n=!0},p(t,[n]){null!==t[1]&&t[1].route===t[7]?o?(o.p(t,n),2&n&&nt(o,1)):(o=Et(t),o.c(),nt(o,1),o.m(e.parentNode,e)):o&&(tt(),ot(o,1,1,(()=>{o=null})),et())},i(t){n||(nt(o),n=!0)},o(t){ot(o),n=!1},d(t){o&&o.d(t),t&&b(e)}}}function Ot(t,e,o){let i,r,{$$slots:s={},$$scope:c}=e,{path:a=""}=e,{component:l=null}=e;const{registerRoute:h,unregisterRoute:d,activeRoute:p}=M($t);u(t,p,(t=>o(1,i=t)));const g=M(mt);u(t,g,(t=>o(4,r=t)));const m={path:a,default:""===a};let $={},w={};var y;return h(m),"undefined"!=typeof window&&(y=()=>{d(m)},z().$$.on_destroy.push(y)),t.$$set=t=>{o(13,e=n(n({},e),f(t))),"path"in t&&o(8,a=t.path),"component"in t&&o(0,l=t.component),"$$scope"in t&&o(9,c=t.$$scope)},t.$$.update=()=>{2&t.$$.dirty&&i&&i.route===m&&o(2,$=i.params);{const{path:t,component:n,...i}=e;o(3,w=i)}},e=f(e),[l,i,$,w,r,p,g,m,a,c,s]}class At extends dt{constructor(t){super(),ht(this,t,Ot,zt,c,{path:8,component:0})}}
/*!
     * Copyright 2019 SmugMug, Inc.
     * Licensed under the terms of the MIT license. Please see LICENSE file in the project root for terms.
     * @license
     */
var Mt,Tt=(function(t){(t.exports=function(t){this.top=t.top,this.left=t.left,this.width=t.width,this.spacing=t.spacing,this.targetRowHeight=t.targetRowHeight,this.targetRowHeightTolerance=t.targetRowHeightTolerance,this.minAspectRatio=this.width/t.targetRowHeight*(1-t.targetRowHeightTolerance),this.maxAspectRatio=this.width/t.targetRowHeight*(1+t.targetRowHeightTolerance),this.edgeCaseMinRowHeight=t.edgeCaseMinRowHeight,this.edgeCaseMaxRowHeight=t.edgeCaseMaxRowHeight,this.widowLayoutStyle=t.widowLayoutStyle,this.isBreakoutRow=t.isBreakoutRow,this.items=[],this.height=0}).prototype={addItem:function(t){var e,n,o,i=this.items.concat(t),r=this.width-(i.length-1)*this.spacing,s=i.reduce((function(t,e){return t+e.aspectRatio}),0),c=r/this.targetRowHeight;return this.isBreakoutRow&&0===this.items.length&&t.aspectRatio>=1?(this.items.push(t),this.completeLayout(r/t.aspectRatio,"justify"),!0):s<this.minAspectRatio?(this.items.push(Object.assign({},t)),!0):s>this.maxAspectRatio?0===this.items.length?(this.items.push(Object.assign({},t)),this.completeLayout(r/s,"justify"),!0):(e=this.width-(this.items.length-1)*this.spacing,n=this.items.reduce((function(t,e){return t+e.aspectRatio}),0),o=e/this.targetRowHeight,Math.abs(s-c)>Math.abs(n-o)?(this.completeLayout(e/n,"justify"),!1):(this.items.push(Object.assign({},t)),this.completeLayout(r/s,"justify"),!0)):(this.items.push(Object.assign({},t)),this.completeLayout(r/s,"justify"),!0)},isLayoutComplete:function(){return this.height>0},completeLayout:function(t,e){var n,o,i,r,s,c=this.left,a=this.width-(this.items.length-1)*this.spacing;(void 0===e||["justify","center","left"].indexOf(e)<0)&&(e="left"),t!==(o=Math.max(this.edgeCaseMinRowHeight,Math.min(t,this.edgeCaseMaxRowHeight)))?(this.height=o,n=a/o/(a/t)):(this.height=t,n=1),this.items.forEach((function(t){t.top=this.top,t.width=t.aspectRatio*this.height*n,t.height=this.height,t.left=c,c+=t.width+this.spacing}),this),"justify"===e?(c-=this.spacing+this.left,i=(c-this.width)/this.items.length,r=this.items.map((function(t,e){return Math.round((e+1)*i)})),1===this.items.length?this.items[0].width-=Math.round(i):this.items.forEach((function(t,e){e>0?(t.left-=r[e-1],t.width-=r[e]-r[e-1]):t.width-=r[e]}))):"center"===e&&(s=(this.width-c)/2,this.items.forEach((function(t){t.left+=s+this.spacing}),this))},forceComplete:function(t,e){"number"==typeof e?this.completeLayout(e,this.widowLayoutStyle):this.completeLayout(this.targetRowHeight,this.widowLayoutStyle)},getItems:function(){return this.items}}}(Mt={exports:{}},Mt.exports),Mt.exports);
/*!
     * Copyright 2019 SmugMug, Inc.
     * Licensed under the terms of the MIT license. Please see LICENSE file in the project root for terms.
     * @license
     */function Bt(t,e){var n;return!1!==t.fullWidthBreakoutRowCadence&&(e._rows.length+1)%t.fullWidthBreakoutRowCadence==0&&(n=!0),new Tt({top:e._containerHeight,left:t.containerPadding.left,width:t.containerWidth-t.containerPadding.left-t.containerPadding.right,spacing:t.boxSpacing.horizontal,targetRowHeight:t.targetRowHeight,targetRowHeightTolerance:t.targetRowHeightTolerance,edgeCaseMinRowHeight:.5*t.targetRowHeight,edgeCaseMaxRowHeight:2*t.targetRowHeight,rightToLeft:!1,isBreakoutRow:n,widowLayoutStyle:t.widowLayoutStyle})}function Wt(t,e,n){return e._rows.push(n),e._layoutItems=e._layoutItems.concat(n.getItems()),e._containerHeight+=n.height+t.boxSpacing.vertical,n.items}var Ft=function(t,e){var n={},o={},i={containerWidth:1060,containerPadding:10,boxSpacing:10,targetRowHeight:320,targetRowHeightTolerance:.25,maxNumRows:Number.POSITIVE_INFINITY,forceAspectRatio:!1,showWidows:!0,fullWidthBreakoutRowCadence:!1,widowLayoutStyle:"left"},r={},s={};return e=e||{},n=Object.assign(i,e),r.top=isNaN(parseFloat(n.containerPadding.top))?n.containerPadding:n.containerPadding.top,r.right=isNaN(parseFloat(n.containerPadding.right))?n.containerPadding:n.containerPadding.right,r.bottom=isNaN(parseFloat(n.containerPadding.bottom))?n.containerPadding:n.containerPadding.bottom,r.left=isNaN(parseFloat(n.containerPadding.left))?n.containerPadding:n.containerPadding.left,s.horizontal=isNaN(parseFloat(n.boxSpacing.horizontal))?n.boxSpacing:n.boxSpacing.horizontal,s.vertical=isNaN(parseFloat(n.boxSpacing.vertical))?n.boxSpacing:n.boxSpacing.vertical,n.containerPadding=r,n.boxSpacing=s,o._layoutItems=[],o._awakeItems=[],o._inViewportItems=[],o._leadingOrphans=[],o._trailingOrphans=[],o._containerHeight=n.containerPadding.top,o._rows=[],o._orphans=[],n._widowCount=0,function(t,e,n){var o,i,r,s=[];return t.forceAspectRatio&&n.forEach((function(e){e.forcedAspectRatio=!0,e.aspectRatio=t.forceAspectRatio})),n.some((function(n,r){if(isNaN(n.aspectRatio))throw new Error("Item "+r+" has an invalid aspect ratio");if(i||(i=Bt(t,e)),o=i.addItem(n),i.isLayoutComplete()){if(s=s.concat(Wt(t,e,i)),e._rows.length>=t.maxNumRows)return i=null,!0;if(i=Bt(t,e),!o&&(o=i.addItem(n),i.isLayoutComplete())){if(s=s.concat(Wt(t,e,i)),e._rows.length>=t.maxNumRows)return i=null,!0;i=Bt(t,e)}}})),i&&i.getItems().length&&t.showWidows&&(e._rows.length?(r=e._rows[e._rows.length-1].isBreakoutRow?e._rows[e._rows.length-1].targetRowHeight:e._rows[e._rows.length-1].height,i.forceComplete(!1,r)):i.forceComplete(!1),s=s.concat(Wt(t,e,i)),t._widowCount=i.getItems().length),e._containerHeight=e._containerHeight-t.boxSpacing.vertical,e._containerHeight=e._containerHeight+t.containerPadding.bottom,{containerHeight:e._containerHeight,widowCount:t._widowCount,boxes:e._layoutItems}}(n,o,t.map((function(t){return t.width&&t.height?{aspectRatio:t.width/t.height}:{aspectRatio:t}})))};function Dt(t,{delay:n=0,duration:o=400,easing:i=e}={}){const r=+getComputedStyle(t).opacity;return{delay:n,duration:o,easing:i,css:t=>"opacity: "+t*r}}function Jt(t){let e,n,o,i,r,s,c,a,u,l,h,d,f=!t[3]&&Ut(t);return{c(){e=_("div"),f&&f.c(),n=R(),o=_("img"),P(o,"alt",i=t[0].title),P(o,"srcset",r=`/img/${t[1]}/${t[0].hash}.jpg, /img/${t[1]}2x/${t[0].hash}.jpg 2x`),o.src!==(s=`/img/${t[1]}/${t[0].hash}.jpg`)&&P(o,"src",s),P(o,"width",c=t[2].width+"px"),P(o,"height",a=t[2].height+"px"),P(o,"class","svelte-2zxlky"),P(e,"class","albumPhoto svelte-2zxlky"),P(e,"style",u=`width: ${t[2].width}px; height: ${t[2].height}px; top: ${t[2].top}px; left: ${t[2].left}px;`)},m(i,r){x(i,e,r),f&&f.m(e,null),y(e,n),y(e,o),l=!0,h||(d=k(o,"load",t[4]),h=!0)},p(t,h){t[3]?f&&(tt(),ot(f,1,1,(()=>{f=null})),et()):f?(f.p(t,h),8&h&&nt(f,1)):(f=Ut(t),f.c(),nt(f,1),f.m(e,n)),(!l||1&h&&i!==(i=t[0].title))&&P(o,"alt",i),(!l||3&h&&r!==(r=`/img/${t[1]}/${t[0].hash}.jpg, /img/${t[1]}2x/${t[0].hash}.jpg 2x`))&&P(o,"srcset",r),(!l||3&h&&o.src!==(s=`/img/${t[1]}/${t[0].hash}.jpg`))&&P(o,"src",s),(!l||4&h&&c!==(c=t[2].width+"px"))&&P(o,"width",c),(!l||4&h&&a!==(a=t[2].height+"px"))&&P(o,"height",a),(!l||4&h&&u!==(u=`width: ${t[2].width}px; height: ${t[2].height}px; top: ${t[2].top}px; left: ${t[2].left}px;`))&&P(e,"style",u)},i(t){l||(nt(f),l=!0)},o(t){ot(f),l=!1},d(t){t&&b(e),f&&f.d(),h=!1,d()}}}function Ut(t){let e,n,o,i,r,s,c;return{c(){e=_("img"),P(e,"class","preload svelte-2zxlky"),P(e,"alt",n=t[0].title),e.src!==(o="data:image/jpeg;base64,"+t[0].tinyJPEG)&&P(e,"src",o),P(e,"width",i=t[2].width+"px"),P(e,"height",r=t[2].height+"px")},m(t,n){x(t,e,n),c=!0},p(t,s){(!c||1&s&&n!==(n=t[0].title))&&P(e,"alt",n),(!c||1&s&&e.src!==(o="data:image/jpeg;base64,"+t[0].tinyJPEG))&&P(e,"src",o),(!c||4&s&&i!==(i=t[2].width+"px"))&&P(e,"width",i),(!c||4&s&&r!==(r=t[2].height+"px"))&&P(e,"height",r)},i(t){c||(s&&s.end(1),c=!0)},o(t){s=rt(e,Dt,{duration:200}),c=!1},d(t){t&&b(e),t&&s&&s.end()}}}function qt(t){let e,n,o=t[0]&&t[2]&&Jt(t);return{c(){o&&o.c(),e=H()},m(t,i){o&&o.m(t,i),x(t,e,i),n=!0},p(t,[n]){t[0]&&t[2]?o?(o.p(t,n),5&n&&nt(o,1)):(o=Jt(t),o.c(),nt(o,1),o.m(e.parentNode,e)):o&&(tt(),ot(o,1,1,(()=>{o=null})),et())},i(t){n||(nt(o),n=!0)},o(t){ot(o),n=!1},d(t){o&&o.d(t),t&&b(e)}}}function Gt(t,e,n){let{photo:o}=e,{size:i="medium"}=e,{dims:r}=e,s=!1;return t.$$set=t=>{"photo"in t&&n(0,o=t.photo),"size"in t&&n(1,i=t.size),"dims"in t&&n(2,r=t.dims)},[o,i,r,s,()=>n(3,s=!0)]}class Vt extends dt{constructor(t){super(),ht(this,t,Gt,qt,c,{photo:0,size:1,dims:2})}}function Yt(t,e,n){const o=t.slice();return o[8]=e[n],o[10]=n,o}function Kt(t){let e,n,o,i,r,s,c,a=t[1].title+"",u=t[2]&&Qt(t);return{c(){e=_("h2"),n=v(a),o=R(),i=_("div"),u&&u.c(),P(e,"class","svelte-13u7rp7"),P(i,"class","albumPhotos svelte-13u7rp7"),P(i,"style",r=`height: ${t[2]?.containerHeight||0}px;`),U((()=>t[4].call(i)))},m(r,a){x(r,e,a),y(e,n),x(r,o,a),x(r,i,a),u&&u.m(i,null),s=function(t,e){"static"===getComputedStyle(t).position&&(t.style.position="relative");const n=_("iframe");n.setAttribute("style","display: block; position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; border: 0; opacity: 0; pointer-events: none; z-index: -1;"),n.setAttribute("aria-hidden","true"),n.tabIndex=-1;const o=S();let i;return o?(n.src="data:text/html,<script>onresize=function(){parent.postMessage(0,'*')}<\/script>",i=k(window,"message",(t=>{t.source===n.contentWindow&&e()}))):(n.src="about:blank",n.onload=()=>{i=k(n.contentWindow,"resize",e)}),y(t,n),()=>{(o||i&&n.contentWindow)&&i(),b(n)}}(i,t[4].bind(i)),c=!0},p(t,e){(!c||2&e)&&a!==(a=t[1].title+"")&&function(t,e){e=""+e,t.wholeText!==e&&(t.data=e)}(n,a),t[2]?u?(u.p(t,e),4&e&&nt(u,1)):(u=Qt(t),u.c(),nt(u,1),u.m(i,null)):u&&(tt(),ot(u,1,1,(()=>{u=null})),et()),(!c||4&e&&r!==(r=`height: ${t[2]?.containerHeight||0}px;`))&&P(i,"style",r)},i(t){c||(nt(u),c=!0)},o(t){ot(u),c=!1},d(t){t&&b(e),t&&b(o),t&&b(i),u&&u.d(),s()}}}function Qt(t){let e,n,o=t[1].photos,i=[];for(let e=0;e<o.length;e+=1)i[e]=Xt(Yt(t,o,e));const r=t=>ot(i[t],1,1,(()=>{i[t]=null}));return{c(){for(let t=0;t<i.length;t+=1)i[t].c();e=H()},m(t,o){for(let e=0;e<i.length;e+=1)i[e].m(t,o);x(t,e,o),n=!0},p(t,n){if(6&n){let s;for(o=t[1].photos,s=0;s<o.length;s+=1){const r=Yt(t,o,s);i[s]?(i[s].p(r,n),nt(i[s],1)):(i[s]=Xt(r),i[s].c(),nt(i[s],1),i[s].m(e.parentNode,e))}for(tt(),s=o.length;s<i.length;s+=1)r(s);et()}},i(t){if(!n){for(let t=0;t<o.length;t+=1)nt(i[t]);n=!0}},o(t){i=i.filter(Boolean);for(let t=0;t<i.length;t+=1)ot(i[t]);n=!1},d(t){!function(t,e){for(let n=0;n<t.length;n+=1)t[n]&&t[n].d(e)}(i,t),t&&b(e)}}}function Xt(t){let e,n;return e=new Vt({props:{photo:t[8],size:"medium",dims:t[2].boxes[t[10]]}}),{c(){ct(e.$$.fragment)},m(t,o){at(e,t,o),n=!0},p(t,n){const o={};2&n&&(o.photo=t[8]),4&n&&(o.dims=t[2].boxes[t[10]]),e.$set(o)},i(t){n||(nt(e.$$.fragment,t),n=!0)},o(t){ot(e.$$.fragment,t),n=!1},d(t){ut(e,t)}}}function Zt(t){let e,n,o=t[1]&&Kt(t);return{c(){o&&o.c(),e=H()},m(t,i){o&&o.m(t,i),x(t,e,i),n=!0},p(t,[n]){t[1]?o?(o.p(t,n),2&n&&nt(o,1)):(o=Kt(t),o.c(),nt(o,1),o.m(e.parentNode,e)):o&&(tt(),ot(o,1,1,(()=>{o=null})),et())},i(t){n||(nt(o),n=!0)},o(t){ot(o),n=!1},d(t){o&&o.d(t),t&&b(e)}}}function te(t,e,n){var o=this&&this.__awaiter||function(t,e,n,o){return new(n||(n=Promise))((function(i,r){function s(t){try{a(o.next(t))}catch(t){r(t)}}function c(t){try{a(o.throw(t))}catch(t){r(t)}}function a(t){var e;t.done?i(t.value):(e=t.value,e instanceof n?e:new n((function(t){t(e)}))).then(s,c)}a((o=o.apply(t,e||[])).next())}))};let i,{identifier:r}=e;function s(t){if(!t||!c)return;const e=c.photos.map((t=>t.aspect));n(2,a=Ft(e,{targetRowHeight:300,containerWidth:t,containerPadding:10,widowLayoutStyle:"center"}))}let c=null,a=null;return O((function(){return o(this,void 0,void 0,(function*(){const t=yield fetch(`${location.origin}/api/album/view/${r}`,{body:JSON.stringify({previews:1}),method:"POST"});if(t.ok)return n(1,c=yield t.json()),c;{const e=yield t.json();console.error(e)}}))})),t.$$set=t=>{"identifier"in t&&n(3,r=t.identifier)},t.$$.update=()=>{1&t.$$.dirty&&s(i),3&t.$$.dirty&&c&&s(i)},[i,c,a,r,function(){i=this.clientWidth,n(0,i)}]}class ee extends dt{constructor(t){super(),ht(this,t,te,Zt,c,{identifier:3})}}function ne(e){let n;return{c(){n=_("div"),n.textContent="This would be a list of albums or something I guess.",P(n,"class","welcome svelte-1fbmti3")},m(t,e){x(t,n,e)},p:t,i:t,o:t,d(t){t&&b(n)}}}class oe extends dt{constructor(t){super(),ht(this,t,null,ne,c,{})}}function ie(e){let n,o,i,r;return n=new At({props:{path:"album/:identifier",component:ee}}),i=new At({props:{component:oe}}),{c(){ct(n.$$.fragment),o=R(),ct(i.$$.fragment)},m(t,e){at(n,t,e),x(t,o,e),at(i,t,e),r=!0},p:t,i(t){r||(nt(n.$$.fragment,t),nt(i.$$.fragment,t),r=!0)},o(t){ot(n.$$.fragment,t),ot(i.$$.fragment,t),r=!1},d(t){ut(n,t),t&&b(o),ut(i,t)}}}function re(t){let e,n,o,i,r;return i=new St({props:{url:t[0],$$slots:{default:[ie]},$$scope:{ctx:t}}}),{c(){e=_("main"),n=_("h1"),n.textContent="Pozzo",o=R(),ct(i.$$.fragment),P(n,"class","svelte-45rfii"),P(e,"class","svelte-45rfii")},m(t,s){x(t,e,s),y(e,n),y(e,o),at(i,e,null),r=!0},p(t,[e]){const n={};1&e&&(n.url=t[0]),2&e&&(n.$$scope={dirty:e,ctx:t}),i.$set(n)},i(t){r||(nt(i.$$.fragment,t),r=!0)},o(t){ot(i.$$.fragment,t),r=!1},d(t){t&&b(e),ut(i)}}}function se(t,e,n){let{url:o=""}=e;return t.$$set=t=>{"url"in t&&n(0,o=t.url)},[o]}return new class extends dt{constructor(t){super(),ht(this,t,se,re,c,{url:0})}}({target:document.body,props:{}})}();
