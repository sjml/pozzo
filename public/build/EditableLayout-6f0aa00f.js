import{Z as t,S as e,i as n,s as r,y as i,w as o,U as s,z as d,b as a,B as l,V as c,_ as u,$ as g,h as f,a0 as p,M as h,a1 as m,x as y,a2 as w,F as v,a3 as b,a4 as E,a5 as x}from"./main-1f341ec6.js";function D(t){const e=t-1;return e*e*e+1}function L(e,n,r={}){const i=getComputedStyle(e),o="none"===i.transform?"":i.transform,s=n.from.width/e.clientWidth,d=n.from.height/e.clientHeight,a=(n.from.left-n.to.left)/s,l=(n.from.top-n.to.top)/d,c=Math.sqrt(a*a+l*l),{delay:u=0,duration:g=(t=>120*Math.sqrt(t)),easing:f=D}=r;return{delay:u,duration:t(g)?g(c):g,easing:f,css:(t,e)=>`transform: ${o} translate(${e*a}px, ${e*l}px);`}}function T(t,e,n){t.dispatchEvent(new CustomEvent("finalize",{detail:{items:e,info:n}}))}function A(t,e,n){t.dispatchEvent(new CustomEvent("consider",{detail:{items:e,info:n}}))}const O="leftForAnother",S="outsideOfAny";function C(t,e,n){t.dispatchEvent(new CustomEvent("draggedEntered",{detail:{indexObj:e,draggedEl:n}}))}function $(t,e,n){t.dispatchEvent(new CustomEvent("draggedLeft",{detail:{draggedEl:e,type:O,theOtherDz:n}}))}function M(t,e,n){t.dispatchEvent(new CustomEvent("draggedOverIndex",{detail:{indexObj:e,draggedEl:n}}))}const k="dragStarted",I="draggedEntered",F="dragEnteredAnother",P="draggedOverIndex",W="draggedLeft",j="draggedLeftAll",z="droppedIntoZone",B="droppedIntoAnother",N="droppedOutsideOfAny",_="dragStopped",R="pointer",q="keyboard";let H=0;function X(){H++}function V(){if(0===H)throw new Error("Bug! trying to decrement when there are no dropzones");H--}const Y="undefined"==typeof window;function Z(t){let e;const n=t.getBoundingClientRect(),r=getComputedStyle(t),i=r.transform;if(i){let o,s,d,a;if(i.startsWith("matrix3d("))e=i.slice(9,-1).split(/, /),o=+e[0],s=+e[5],d=+e[12],a=+e[13];else{if(!i.startsWith("matrix("))return n;e=i.slice(7,-1).split(/, /),o=+e[0],s=+e[3],d=+e[4],a=+e[5]}const l=r.transformOrigin,c=n.x-d-(1-o)*parseFloat(l),u=n.y-a-(1-s)*parseFloat(l.slice(l.indexOf(" ")+1)),g=o?n.width/o:t.offsetWidth,f=s?n.height/s:t.offsetHeight;return{x:c,y:u,width:g,height:f,top:u,right:c+g,bottom:u+f,left:c}}return n}function U(t){const e=Z(t);return{top:e.top+window.scrollY,bottom:e.bottom+window.scrollY,left:e.left+window.scrollX,right:e.right+window.scrollX}}function G(t){const e=t.getBoundingClientRect();return{top:e.top+window.scrollY,bottom:e.bottom+window.scrollY,left:e.left+window.scrollX,right:e.right+window.scrollX}}function J(t,e){return t.y<=e.bottom&&t.y>=e.top&&t.x>=e.left&&t.x<=e.right}function K(t){return{x:((e=G(t)).left+e.right)/2,y:(e.top+e.bottom)/2};var e}function Q(t,e){return J(K(t),U(e))}function tt(t,e){const n=K(t),r=K(e);return i=n,o=r,Math.sqrt(Math.pow(i.x-o.x,2)+Math.pow(i.y-o.y,2));var i,o}let et;function nt(){et=new Map}function rt(t,e){if(!Q(t,e))return null;const n=e.children;if(0===n.length)return{index:0,isProximityBased:!0};const r=function(t){const e=Array.from(t.children).findIndex((t=>t.getAttribute("data-is-dnd-shadow-item")));if(e>=0)return et.has(t)||et.set(t,new Map),et.get(t).set(e,U(t.children[e])),e}(e);for(let i=0;i<n.length;i++)if(Q(t,n[i])){const n=et.has(e)&&et.get(e).get(i);return n&&!J(K(t),n)?{index:r,isProximityBased:!1}:{index:i,isProximityBased:!1}}let i,o=Number.MAX_VALUE;for(let e=0;e<n.length;e++){const r=tt(t,n[e]);r<o&&(o=r,i=e)}return{index:i,isProximityBased:!0}}nt();function it(){let t;function e(){t={directionObj:void 0,stepPx:0}}function n(e){const{directionObj:r,stepPx:i}=t;r&&(e.scrollBy(r.x*i,r.y*i),window.requestAnimationFrame((()=>n(e))))}function r(t){return 25-t}return e(),{scrollIfNeeded:function(i,o){if(!o)return!1;const s=function(t,e){const n=G(e);return J(t,n)?{top:t.y-n.top,bottom:n.bottom-t.y,left:t.x-n.left,right:Math.min(n.right,document.documentElement.clientWidth)-t.x}:null}(i,o);if(null===s)return e(),!1;const d=!!t.directionObj;let[a,l]=[!1,!1];return o.scrollHeight>o.clientHeight&&(s.bottom<25?(a=!0,t.directionObj={x:0,y:1},t.stepPx=r(s.bottom)):s.top<25&&(a=!0,t.directionObj={x:0,y:-1},t.stepPx=r(s.top)),!d&&a)||o.scrollWidth>o.clientWidth&&(s.right<25?(l=!0,t.directionObj={x:1,y:0},t.stepPx=r(s.right)):s.left<25&&(l=!0,t.directionObj={x:-1,y:0},t.stepPx=r(s.left)),!d&&l)?(n(o),!0):(e(),!1)},resetScrolling:e}}function ot(t){return JSON.stringify(t,null,2)}function st(t){if(!t)throw new Error("cannot get depth of a falsy node");return dt(t,0)}function dt(t,e=0){return t.parentElement?dt(t.parentElement,e+1):e-1}const{scrollIfNeeded:at,resetScrolling:lt}=it();let ct;function ut(t,e,n=200){let r,i,o,s=!1;const d=Array.from(e).sort(((t,e)=>st(e)-st(t)));!function e(){const a=K(t);if(!at(a,r)&&o&&Math.abs(o.x-a.x)<10&&Math.abs(o.y-a.y)<10)return void(ct=window.setTimeout(e,n));if(function(t){const e=G(t);return e.right<0||e.left>document.documentElement.scrollWidth||e.bottom<0||e.top>document.documentElement.scrollHeight}(t))return void function(t){window.dispatchEvent(new CustomEvent("draggedLeftDocument",{detail:{draggedEl:t}}))}(t);o=a;let l=!1;for(const e of d){const n=rt(t,e);if(null===n)continue;const{index:o}=n;l=!0,e!==r?(r&&$(r,t,e),C(e,n,t),r=e):o!==i&&(M(e,n,t),i=o);break}!l&&s&&r?(!function(t,e){t.dispatchEvent(new CustomEvent("draggedLeft",{detail:{draggedEl:e,type:S}}))}(r,t),r=void 0,i=void 0,s=!1):s=!0,ct=window.setTimeout(e,n)}()}let gt;function ft(t){const e=t.touches?t.touches[0]:t;gt={x:e.clientX,y:e.clientY}}const{scrollIfNeeded:pt,resetScrolling:ht}=it();let mt;function yt(){gt&&pt(gt,document.documentElement),mt=window.setTimeout(yt,300)}function wt(t){return`${t} 0.2s ease`}function vt(t,e,n,r,i){const o=e.getBoundingClientRect(),s=t.getBoundingClientRect(),d=o.width-s.width,a=o.height-s.height;if(d||a){const e={left:(n-s.left)/s.width,top:(r-s.top)/s.height};t.style.height=`${o.height}px`,t.style.width=`${o.width}px`,t.style.left=parseFloat(t.style.left)-e.left*d+"px",t.style.top=parseFloat(t.style.top)-e.top*a+"px"}bt(e,t),i()}function bt(t,e){const n=window.getComputedStyle(t);Array.from(n).filter((t=>t.startsWith("background")||t.startsWith("padding")||t.startsWith("font")||t.startsWith("text")||t.startsWith("align")||t.startsWith("justify")||t.startsWith("display")||t.startsWith("flex")||t.startsWith("border")||"opacity"===t||"color"===t||"list-style-type"===t)).forEach((t=>e.style.setProperty(t,n.getPropertyValue(t),n.getPropertyPriority(t))))}function Et(t,e){t.draggable=!1,t.ondragstart=()=>!1,e?(t.style.userSelect="",t.style.WebkitUserSelect="",t.style.cursor=""):(t.style.userSelect="none",t.style.WebkitUserSelect="none",t.style.cursor="grab")}function xt(t,e=(()=>{}),n=(()=>[])){t.forEach((t=>{const r=e(t);Object.keys(r).forEach((e=>{t.style[e]=r[e]})),n(t).forEach((e=>t.classList.add(e)))}))}function Dt(t,e=(()=>{}),n=(()=>[])){t.forEach((t=>{const r=e(t);Object.keys(r).forEach((e=>{t.style[e]=""})),n(t).forEach((e=>t.classList.contains(e)&&t.classList.remove(e)))}))}const Lt={outline:"rgba(255, 255, 102, 0.7) solid 2px"};let Tt,At,Ot,St,Ct,$t,Mt,kt,It,Ft,Pt,Wt=!1,jt=!1,zt=!1;const Bt=new Map,Nt=new Map,_t=new WeakMap;function Rt(t,e){Bt.get(e).delete(t),V(),0===Bt.get(e).size&&Bt.delete(e)}function qt(){window.addEventListener("mousemove",ft),window.addEventListener("touchmove",ft),yt();const t=Bt.get(St);for(const e of t)e.addEventListener("draggedEntered",Vt),e.addEventListener("draggedLeft",Yt),e.addEventListener("draggedOverIndex",Zt);window.addEventListener("draggedLeftDocument",Gt);const e=Math.max(100,...Array.from(t.keys()).map((t=>Nt.get(t).dropAnimationDurationMs)));ut(At,t,1.07*e)}function Ht(){window.removeEventListener("mousemove",ft),window.removeEventListener("touchmove",ft),gt=void 0,window.clearTimeout(mt),ht();const t=Bt.get(St);for(const e of t)e.removeEventListener("draggedEntered",Vt),e.removeEventListener("draggedLeft",Yt),e.removeEventListener("draggedOverIndex",Zt);window.removeEventListener("draggedLeftDocument",Gt),clearTimeout(ct),lt(),nt()}function Xt(t){return t.findIndex((t=>!!t.isDndShadowItem&&"id:dnd-shadow-placeholder-0000"!==t.id))}function Vt(t){let{items:e,dropFromOthersDisabled:n}=Nt.get(t.currentTarget);if(n&&t.currentTarget!==Ct)return;if(zt=!1,e=e.filter((t=>t.id!==Mt.id)),Ct!==t.currentTarget){const t=Nt.get(Ct).items.filter((t=>!t.isDndShadowItem));A(Ct,t,{trigger:F,id:Ot.id,source:R})}else{const t=function(t){return t.findIndex((t=>"id:dnd-shadow-placeholder-0000"===t.id))}(e);-1!==t&&e.splice(t,1)}const{index:r,isProximityBased:i}=t.detail.indexObj,o=i&&r===t.currentTarget.children.length-1?r+1:r;kt=t.currentTarget,e.splice(o,0,Mt),A(t.currentTarget,e,{trigger:I,id:Ot.id,source:R})}function Yt(t){const{items:e,dropFromOthersDisabled:n}=Nt.get(t.currentTarget);if(n&&t.currentTarget!==Ct)return;const r=Xt(e),i=e.splice(r,1)[0];kt=void 0;const{type:o,theOtherDz:s}=t.detail;if(o===S||o===O&&s!==Ct&&Nt.get(s).dropFromOthersDisabled){zt=!0,kt=Ct;const t=Nt.get(Ct).items;t.splice($t,0,i),A(Ct,t,{trigger:j,id:Ot.id,source:R})}A(t.currentTarget,e,{trigger:W,id:Ot.id,source:R})}function Zt(t){const{items:e,dropFromOthersDisabled:n}=Nt.get(t.currentTarget);if(n&&t.currentTarget!==Ct)return;zt=!1;const{index:r}=t.detail.indexObj,i=Xt(e);e.splice(i,1),e.splice(r,0,Mt),A(t.currentTarget,e,{trigger:P,id:Ot.id,source:R})}function Ut(t){t.preventDefault();const e=t.touches?t.touches[0]:t;Ft={x:e.clientX,y:e.clientY},At.style.transform=`translate3d(${Ft.x-It.x}px, ${Ft.y-It.y}px, 0)`}function Gt(){jt=!0,window.removeEventListener("mousemove",Ut),window.removeEventListener("touchmove",Ut),window.removeEventListener("mouseup",Gt),window.removeEventListener("touchend",Gt),Ht(),function(t){t.style.cursor="grab"}(At),kt||(kt=Ct);let{items:t,type:e}=Nt.get(kt);Dt(Bt.get(e),(t=>Nt.get(t).dropTargetStyle),(t=>Nt.get(t).dropTargetClasses));let n=Xt(t);-1===n&&(n=$t),t=t.map((t=>t.isDndShadowItem?Ot:t)),function(t,e){const n=Z(kt.children[t]),r={x:n.left-parseFloat(At.style.left),y:n.top-parseFloat(At.style.top)},{dropAnimationDurationMs:i}=Nt.get(kt),o=`transform ${i}ms ease`;At.style.transition=At.style.transition?At.style.transition+","+o:o,At.style.transform=`translate3d(${r.x}px, ${r.y}px, 0)`,window.setTimeout(e,i)}(n,(function(){var e;Pt(),T(kt,t,{trigger:zt?N:z,id:Ot.id,source:R}),kt!==Ct&&T(Ct,Nt.get(Ct).items,{trigger:B,id:Ot.id,source:R}),(e=kt.children[n]).style.visibility="",e.removeAttribute("data-is-dnd-shadow-item"),At.remove(),Tt.remove(),At=void 0,Tt=void 0,Ot=void 0,St=void 0,Ct=void 0,$t=void 0,Mt=void 0,kt=void 0,It=void 0,Ft=void 0,Wt=!1,jt=!1,Pt=void 0,zt=!1}))}function Jt(t,e){const n={items:void 0,type:void 0,flipDurationMs:0,dragDisabled:!1,dropFromOthersDisabled:!1,dropTargetStyle:Lt,dropTargetClasses:[],transformDraggedElement:()=>{}};let r=new Map;function i(){window.removeEventListener("mousemove",s),window.removeEventListener("touchmove",s),window.removeEventListener("mouseup",o),window.removeEventListener("touchend",o)}function o(){i(),Tt=void 0,It=void 0,Ft=void 0}function s(t){t.preventDefault();const e=t.touches?t.touches[0]:t;Ft={x:e.clientX,y:e.clientY},(Math.abs(Ft.x-It.x)>=3||Math.abs(Ft.y-It.y)>=3)&&(i(),function(){Wt=!0;const t=r.get(Tt);$t=t,Ct=Tt.parentElement;const{items:e,type:i}=n;Ot={...e[t]},St=i,Mt={...Ot,isDndShadowItem:!0};const o={...Mt,id:"id:dnd-shadow-placeholder-0000"};function s(){var t;At.parentElement?window.requestAnimationFrame(s):(document.body.appendChild(At),At.focus(),qt(),(t=Tt).style.display="none",t.style.position="fixed",t.style.zIndex="-5",document.body.appendChild(Tt))}At=function(t){const e=t.getBoundingClientRect(),n=t.cloneNode(!0);return bt(t,n),n.id="dnd-action-dragged-el",n.style.position="fixed",n.style.top=`${e.top}px`,n.style.left=`${e.left}px`,n.style.margin="0",n.style.boxSizing="border-box",n.style.height=`${e.height}px`,n.style.width=`${e.width}px`,n.style.transition=`${wt("width")}, ${wt("height")}, ${wt("background-color")}, ${wt("opacity")}, ${wt("color")} `,window.setTimeout((()=>n.style.transition+=`, ${wt("top")}, ${wt("left")}`),0),n.style.zIndex="9999",n.style.cursor="grabbing",n}(Tt),window.requestAnimationFrame(s),xt(Array.from(Bt.get(n.type)).filter((t=>t===Ct||!Nt.get(t).dropFromOthersDisabled)),(t=>Nt.get(t).dropTargetStyle),(t=>Nt.get(t).dropTargetClasses)),e.splice(t,1,o),Pt=function(t){const e=t.style.minHeight;t.style.minHeight=window.getComputedStyle(t).getPropertyValue("height");const n=t.style.minWidth;return t.style.minWidth=window.getComputedStyle(t).getPropertyValue("width"),function(){t.style.minHeight=e,t.style.minWidth=n}}(Ct),A(Ct,e,{trigger:k,id:Ot.id,source:R}),window.addEventListener("mousemove",Ut,{passive:!1}),window.addEventListener("touchmove",Ut,{passive:!1,capture:!1}),window.addEventListener("mouseup",Gt,{passive:!1}),window.addEventListener("touchend",Gt,{passive:!1})}())}function d(t){if(t.target!==t.currentTarget&&(void 0!==t.target.value||t.target.isContentEditable))return;if(t.button)return;if(Wt)return;t.stopPropagation();const e=t.touches?t.touches[0]:t;It={x:e.clientX,y:e.clientY},Ft={...It},Tt=t.currentTarget,window.addEventListener("mousemove",s,{passive:!1}),window.addEventListener("touchmove",s,{passive:!1,capture:!1}),window.addEventListener("mouseup",o,{passive:!1}),window.addEventListener("touchend",o,{passive:!1})}function a({items:e,flipDurationMs:i=0,type:o="--any--",dragDisabled:s=!1,dropFromOthersDisabled:a=!1,dropTargetStyle:l=Lt,dropTargetClasses:c=[],transformDraggedElement:u=(()=>{})}){var g,f;n.dropAnimationDurationMs=i,n.type&&o!==n.type&&Rt(t,n.type),n.type=o,g=t,f=o,Bt.has(f)||Bt.set(f,new Set),Bt.get(f).has(g)||(Bt.get(f).add(g),X()),n.items=[...e],n.dragDisabled=s,n.transformDraggedElement=u,!Wt||jt||function(t,e){if(Object.keys(t).length!==Object.keys(e).length)return!1;for(const n in t)if(!{}.hasOwnProperty.call(e,n)||e[n]!==t[n])return!1;return!0}(l,n.dropTargetStyle)&&function(t,e){if(t.length!==e.length)return!1;for(let n=0;n<t.length;n++)if(t[n]!==e[n])return!1;return!0}(c,n.dropTargetClasses)||(Dt([t],(()=>n.dropTargetStyle),(()=>c)),xt([t],(()=>l),(()=>c))),n.dropTargetStyle=l,n.dropTargetClasses=[...c],Wt&&n.dropFromOthersDisabled!==a&&(a?Dt([t],(t=>Nt.get(t).dropTargetStyle),(t=>Nt.get(t).dropTargetClasses)):xt([t],(t=>Nt.get(t).dropTargetStyle),(t=>Nt.get(t).dropTargetClasses))),n.dropFromOthersDisabled=a,Nt.set(t,n);const p=Xt(n.items);for(let e=0;e<t.children.length;e++){const i=t.children[e];Et(i,s),e!==p?(i.removeEventListener("mousedown",_t.get(i)),i.removeEventListener("touchstart",_t.get(i)),s||(i.addEventListener("mousedown",d),i.addEventListener("touchstart",d),_t.set(i,d)),r.set(i,e)):(vt(At,i,Ft.x,Ft.y,(()=>n.transformDraggedElement(At,Ot,e))),(h=i).style.visibility="hidden",h.setAttribute("data-is-dnd-shadow-item","true"))}var h}return a(e),{update:t=>{a(t)},destroy:()=>{Rt(t,n.type),Nt.delete(t)}}}const Kt={DND_ZONE_ACTIVE:"dnd-zone-active",DND_ZONE_DRAG_DISABLED:"dnd-zone-drag-disabled"},Qt={[Kt.DND_ZONE_ACTIVE]:"Tab to one the items and press space-bar or enter to start dragging it",[Kt.DND_ZONE_DRAG_DISABLED]:"This is a disabled drag and drop list"};let te;function ee(){te=document.createElement("div"),te.id="dnd-action-aria-alert",te.style.position="fixed",te.style.bottom="0",te.style.left="0",te.style.zIndex="-5",te.style.opacity="0",te.style.height="0",te.style.width="0",te.setAttribute("role","alert"),document.body.prepend(te),Object.entries(Qt).forEach((([t,e])=>document.body.prepend(function(t,e){const n=document.createElement("div");return n.id=t,n.innerHTML=`<p>${e}</p>`,n.style.display="none",n.style.position="fixed",n.style.zIndex="-5",n}(t,e))))}function ne(t){te.innerHTML="";const e=document.createTextNode(t);te.appendChild(e),te.style.display="none",te.style.display="inline"}const re={outline:"rgba(255, 255, 102, 0.7) solid 2px"};let ie,oe,se,de,ae=!1,le="",ce="";const ue=new WeakSet,ge=new WeakMap,fe=new WeakMap,pe=new Map,he=new Map,me=new Map,ye=Y?null:("complete"===document.readyState?ee():window.addEventListener("DOMContentLoaded",ee),{...Kt});function we(t,e){oe===t&&De(),me.get(e).delete(t),V(),0===me.get(e).size&&me.delete(e),0===me.size&&(window.removeEventListener("keydown",ve),window.removeEventListener("click",be))}function ve(t){if(ae)switch(t.key){case"Escape":De()}}function be(){ae&&(ue.has(document.activeElement)||De())}function Ee(t){if(!ae)return;const e=t.currentTarget;if(e===oe)return;le=e.getAttribute("aria-label")||"";const{items:n}=he.get(oe),r=n.find((t=>t.id===de)),i=n.indexOf(r),o=n.splice(i,1)[0],{items:s,autoAriaDisabled:d}=he.get(e);e.getBoundingClientRect().top<oe.getBoundingClientRect().top||e.getBoundingClientRect().left<oe.getBoundingClientRect().left?(s.push(o),d||ne(`Moved item ${ce} to the end of the list ${le}`)):(s.unshift(o),d||ne(`Moved item ${ce} to the beginning of the list ${le}`));T(oe,n,{trigger:B,id:de,source:q}),T(e,s,{trigger:z,id:de,source:q}),oe=e}function xe(){pe.forEach((({update:t},e)=>t(he.get(e))))}function De(t=!0){he.get(oe).autoAriaDisabled||ne(`Stopped dragging item ${ce}`),ue.has(document.activeElement)&&document.activeElement.blur(),t&&A(oe,he.get(oe).items,{trigger:_,id:de,source:q}),Dt(me.get(ie),(t=>he.get(t).dropTargetStyle),(t=>he.get(t).dropTargetClasses)),se=null,de=null,ce="",ie=null,oe=null,le="",ae=!1,xe()}function Le(t,e){const n={items:void 0,type:void 0,dragDisabled:!1,dropFromOthersDisabled:!1,dropTargetStyle:re,dropTargetClasses:[],autoAriaDisabled:!1};function r(t,e,n){t.length<=1||t.splice(n,1,t.splice(e,1,t[n])[0])}function i(e){switch(e.key){case"Enter":case" ":if((void 0!==e.target.disabled||e.target.href||e.target.isContentEditable)&&!ue.has(e.target))return;e.preventDefault(),e.stopPropagation(),ae?De():o(e);break;case"ArrowDown":case"ArrowRight":{if(!ae)return;e.preventDefault(),e.stopPropagation();const{items:i}=he.get(t),o=Array.from(t.children),s=o.indexOf(e.currentTarget);s<o.length-1&&(n.autoAriaDisabled||ne(`Moved item ${ce} to position ${s+2} in the list ${le}`),r(i,s,s+1),T(t,i,{trigger:z,id:de,source:q}));break}case"ArrowUp":case"ArrowLeft":{if(!ae)return;e.preventDefault(),e.stopPropagation();const{items:i}=he.get(t),o=Array.from(t.children).indexOf(e.currentTarget);o>0&&(n.autoAriaDisabled||ne(`Moved item ${ce} to position ${o} in the list ${le}`),r(i,o,o-1),T(t,i,{trigger:z,id:de,source:q}));break}}}function o(e){!function(e){const{items:n}=he.get(t),r=Array.from(t.children),i=r.indexOf(e);se=e,se.tabIndex=0,de=n[i].id,ce=r[i].getAttribute("aria-label")||""}(e.currentTarget),oe=t,ie=n.type,ae=!0;const r=Array.from(me.get(n.type)).filter((t=>t===oe||!he.get(t).dropFromOthersDisabled));if(xt(r,(t=>he.get(t).dropTargetStyle),(t=>he.get(t).dropTargetClasses)),!n.autoAriaDisabled){let t=`Started dragging item ${ce}. Use the arrow keys to move it within its list ${le}`;r.length>1&&(t+=", or tab to another list in order to move the item into it"),ne(t)}A(t,he.get(t).items,{trigger:k,id:de,source:q}),xe()}function s(t){ae&&t.currentTarget!==se&&(t.stopPropagation(),De(!1),o(t))}function d({items:e=[],type:r="--any--",dragDisabled:o=!1,dropFromOthersDisabled:d=!1,dropTargetStyle:a=re,dropTargetClasses:l=[],autoAriaDisabled:c=!1}){var u,g;n.items=[...e],n.dragDisabled=o,n.dropFromOthersDisabled=d,n.dropTargetStyle=a,n.dropTargetClasses=l,n.autoAriaDisabled=c,c||(t.setAttribute("aria-disabled",o),t.setAttribute("role","list"),t.setAttribute("aria-describedby",o?ye.DND_ZONE_DRAG_DISABLED:ye.DND_ZONE_ACTIVE)),n.type&&r!==n.type&&we(t,n.type),n.type=r,u=t,g=r,0===me.size&&(window.addEventListener("keydown",ve),window.addEventListener("click",be)),me.has(g)||me.set(g,new Set),me.get(g).has(u)||(me.get(g).add(u),X()),he.set(t,n),t.tabIndex=ae&&(t===oe||se.contains(t)||n.dropFromOthersDisabled||oe&&n.type!==he.get(oe).type)?-1:0,t.addEventListener("focus",Ee);for(let e=0;e<t.children.length;e++){const r=t.children[e];ue.add(r),r.tabIndex=ae?-1:0,c||r.setAttribute("role","listitem"),r.removeEventListener("keydown",ge.get(r)),r.removeEventListener("click",fe.get(r)),o||(r.addEventListener("keydown",i),ge.set(r,i),r.addEventListener("click",s),fe.set(r,s)),ae&&n.items[e].id===de&&(se=r,se.tabIndex=0,r.focus())}}d(e);const a={update:t=>{d(t)},destroy:()=>{we(t,n.type),he.delete(t),pe.delete(t)}};return pe.set(t,a),a}function Te(t,e){Ae(e);const n=Jt(t,e),r=Le(t,e);return{update:t=>{Ae(t),n.update(t),r.update(t)},destroy:()=>{n.destroy(),r.destroy()}}}function Ae(t){const{items:e,flipDurationMs:n,type:r,dragDisabled:i,dropFromOthersDisabled:o,dropTargetStyle:s,dropTargetClasses:d,transformDraggedElement:a,autoAriaDisabled:l,...c}=t;if(Object.keys(c).length>0&&console.warn("dndzone will ignore unknown options",c),!e)throw new Error("no 'items' key provided to dndzone");const u=e.find((t=>!{}.hasOwnProperty.call(t,"id")));if(u)throw new Error(`missing 'id' property for item ${ot(u)}`);if(d&&!Array.isArray(d))throw new Error(`dropTargetClasses should be an array but instead it is a ${typeof d}, ${ot(d)}`)}function Oe(t,e,n){const r=t.slice();return r[5]=e[n],r}function Se(t){let e;return{c(){e=i("div"),d(e,"class","placeholder svelte-f8uj3v")},m(t,n){a(t,e,n)},p:y,d(t){t&&f(e)}}}function Ce(t){let e,n,r,o,s,l;return{c(){e=i("img"),d(e,"alt",n=t[5].title??""),d(e,"draggable","false"),d(e,"srcset",r=E(t[1],t[5].hash,t[5].uniq)+", "+`${E(t[1]+"2x",t[5].hash,t[5].uniq)} 2x`),e.src!==(o=E(t[1],t[5].hash,t[5].uniq))&&d(e,"src",o),d(e,"class","svelte-f8uj3v")},m(t,n){a(t,e,n),s||(l=h(e,"dragstart",x(Ie)),s=!0)},p(t,i){1&i&&n!==(n=t[5].title??"")&&d(e,"alt",n),3&i&&r!==(r=E(t[1],t[5].hash,t[5].uniq)+", "+`${E(t[1]+"2x",t[5].hash,t[5].uniq)} 2x`)&&d(e,"srcset",r),3&i&&e.src!==(o=E(t[1],t[5].hash,t[5].uniq))&&d(e,"src",o)},d(t){t&&f(e),s=!1,l()}}}function $e(t,e){let n,r,p,h,m,w,v=(e[5].title??"")+"",b=y;function E(t,e){return t[5].hash&&t[5].uniq?Ce:Se}let x=E(e),D=x(e);return{key:t,first:null,c(){n=i("div"),D.c(),r=o(),p=i("div"),h=s(v),m=o(),d(n,"class","editablePhoto svelte-f8uj3v"),this.first=n},m(t,e){a(t,n,e),D.m(n,null),l(n,r),l(n,p),l(p,h),l(n,m)},p(t,i){x===(x=E(e=t))&&D?D.p(e,i):(D.d(1),D=x(e),D&&(D.c(),D.m(n,r))),1&i&&v!==(v=(e[5].title??"")+"")&&c(h,v)},r(){w=n.getBoundingClientRect()},f(){u(n),b()},a(){b(),b=g(n,w,L,{duration:ke})},d(t){t&&f(n),D.d()}}}function Me(e){let n,r,o,s,l=[],c=new Map,u=e[0];const g=t=>t[5].id;for(let t=0;t<u.length;t+=1){let n=Oe(e,u,t),r=g(n);c.set(r,l[t]=$e(r,n))}return{c(){n=i("div");for(let t=0;t<l.length;t+=1)l[t].c();d(n,"class","editableLayout svelte-f8uj3v")},m(t,i){a(t,n,i);for(let t=0;t<l.length;t+=1)l[t].m(n,null);o||(s=[p(r=Te.call(null,n,{items:e[0],flipDurationMs:ke,dropTargetStyle:{}})),h(n,"consider",e[2]),h(n,"finalize",e[3])],o=!0)},p(e,[i]){if(3&i){u=e[0];for(let t=0;t<l.length;t+=1)l[t].r();l=m(l,i,g,1,e,u,c,n,b,$e,null,Oe);for(let t=0;t<l.length;t+=1)l[t].a()}r&&t(r.update)&&1&i&&r.update.call(null,{items:e[0],flipDurationMs:ke,dropTargetStyle:{}})},i:y,o:y,d(t){t&&f(n);for(let t=0;t<l.length;t+=1)l[t].d();o=!1,w(s)}}}const ke=200,Ie=()=>{};function Fe(t,e,n){const r=v();let{photoList:i=[]}=e,{size:o="medium"}=e;return t.$$set=t=>{"photoList"in t&&n(0,i=t.photoList),"size"in t&&n(1,o=t.size)},[i,o,function(t){n(0,i=t.detail.items)},function(t){n(0,i=t.detail.items),r("reordered",{newPhotos:i})}]}export default class extends e{constructor(t){super(),n(this,t,Fe,Me,r,{photoList:0,size:1})}}