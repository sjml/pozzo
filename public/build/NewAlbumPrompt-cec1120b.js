import{S as t,i as s,s as e,a6 as a,c as n,m as i,t as l,a as c,d as u,F as o,a7 as r,a8 as d,y as f,U as m,w as h,z as v,b as p,B as b,K as w,M as $,a5 as k,V as y,h as g,a2 as x,R as P,a9 as A}from"./main-007fa5d6.js";function D(t){let s,e,a,n,i,l,c,u,o,r,d,P,A,D,z,I,O,S,T,_;return{c(){s=f("div"),e=f("form"),a=f("label"),n=m("Album Name:\n                "),i=f("input"),l=h(),c=f("div"),u=f("label"),o=f("input"),r=m("\n                    Private"),d=h(),P=f("button"),A=m("Create Album"),z=h(),I=f("div"),O=m(t[3]),S=m(" "),v(i,"type","text"),i.disabled=t[2],v(i,"class","svelte-1kl6n6w"),v(a,"class","svelte-1kl6n6w"),v(o,"type","checkbox"),v(u,"class","svelte-1kl6n6w"),v(c,"class","visibilityToggle"),v(P,"type","submit"),P.disabled=D=t[2]||t[1].length<=0,v(P,"class","svelte-1kl6n6w"),v(I,"class","new-album-message svelte-1kl6n6w"),v(s,"class","newAlbumPrompt svelte-1kl6n6w")},m(f,m){p(f,s,m),b(s,e),b(e,a),b(a,n),b(a,i),t[7](i),w(i,t[1]),b(e,l),b(e,c),b(c,u),b(u,o),o.checked=t[4],b(u,r),b(e,d),b(e,P),b(P,A),b(s,z),b(s,I),b(I,O),b(I,S),T||(_=[$(i,"input",t[8]),$(o,"change",t[9]),$(e,"submit",k(t[6]))],T=!0)},p(t,s){4&s&&(i.disabled=t[2]),2&s&&i.value!==t[1]&&w(i,t[1]),16&s&&(o.checked=t[4]),6&s&&D!==(D=t[2]||t[1].length<=0)&&(P.disabled=D),8&s&&y(O,t[3])},d(e){e&&g(s),t[7](null),T=!1,x(_)}}}function z(t){let s,e;return s=new a({props:{$$slots:{default:[D]},$$scope:{ctx:t}}}),s.$on("clickedOutside",t[10]),{c(){n(s.$$.fragment)},m(t,a){i(s,t,a),e=!0},p(t,[e]){const a={};8223&e&&(a.$$scope={dirty:e,ctx:t}),s.$set(a)},i(t){e||(l(s.$$.fragment,t),e=!0)},o(t){c(s.$$.fragment,t),e=!1},d(t){u(s,t)}}}function I(t,s,e){var a=this&&this.__awaiter||function(t,s,e,a){return new(e||(e=Promise))((function(n,i){function l(t){try{u(a.next(t))}catch(t){i(t)}}function c(t){try{u(a.throw(t))}catch(t){i(t)}}function u(t){var s;t.done?n(t.value):(s=t.value,s instanceof e?s:new e((function(t){t(s)}))).then(l,c)}u((a=a.apply(t,s||[])).next())}))};const n=o();let i=null,l="",c=!1,u="",f=!1;r((function(){return a(this,void 0,void 0,(function*(){e(1,l=""),e(3,u=""),yield d(),i.focus()}))}));return[i,l,c,u,f,n,function(){return a(this,void 0,void 0,(function*(){e(2,c=!0);const t=yield P("/album/new",{params:{title:l,isPrivate:f},method:"POST",authorize:!0});t.success?n("done",{newAlbumID:t.data.albumID}):e(3,u="Duplicate name!"),e(2,c=!1)}))},function(t){A[t?"unshift":"push"]((()=>{i=t,e(0,i)}))},function(){l=this.value,e(1,l)},function(){f=this.checked,e(4,f)},()=>n("dismissed")]}export default class extends t{constructor(t){super(),s(this,t,I,z,e,{})}}
