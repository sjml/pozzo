import{S as t,i as e,s,a6 as a,c as n,m as i,t as l,a as c,d as u,F as o,a7 as r,a8 as d,y as f,U as m,w as h,z as v,b as p,B as b,K as w,M as $,a5 as k,V as y,h as g,a2 as x,R as P,a9 as A}from"./main-1f341ec6.js";function D(t){let e,s,a,n,i,l,c,u,o,r,d,P,A,D,z,I,O,S,T,_;return{c(){e=f("div"),s=f("form"),a=f("label"),n=m("Album Name:\n                "),i=f("input"),l=h(),c=f("div"),u=f("label"),o=f("input"),r=m("\n                    Private"),d=h(),P=f("button"),A=m("Create Album"),z=h(),I=f("div"),O=m(t[3]),S=m(" "),v(i,"type","text"),i.disabled=t[2],v(i,"class","svelte-1kl6n6w"),v(a,"class","svelte-1kl6n6w"),v(o,"type","checkbox"),v(u,"class","svelte-1kl6n6w"),v(c,"class","visibilityToggle"),v(P,"type","submit"),P.disabled=D=t[2]||t[1].length<=0,v(P,"class","svelte-1kl6n6w"),v(I,"class","new-album-message svelte-1kl6n6w"),v(e,"class","newAlbumPrompt svelte-1kl6n6w")},m(f,m){p(f,e,m),b(e,s),b(s,a),b(a,n),b(a,i),t[7](i),w(i,t[1]),b(s,l),b(s,c),b(c,u),b(u,o),o.checked=t[4],b(u,r),b(s,d),b(s,P),b(P,A),b(e,z),b(e,I),b(I,O),b(I,S),T||(_=[$(i,"input",t[8]),$(o,"change",t[9]),$(s,"submit",k(t[6]))],T=!0)},p(t,e){4&e&&(i.disabled=t[2]),2&e&&i.value!==t[1]&&w(i,t[1]),16&e&&(o.checked=t[4]),6&e&&D!==(D=t[2]||t[1].length<=0)&&(P.disabled=D),8&e&&y(O,t[3])},d(s){s&&g(e),t[7](null),T=!1,x(_)}}}function z(t){let e,s;return e=new a({props:{$$slots:{default:[D]},$$scope:{ctx:t}}}),e.$on("clickedOutside",t[10]),{c(){n(e.$$.fragment)},m(t,a){i(e,t,a),s=!0},p(t,[s]){const a={};8223&s&&(a.$$scope={dirty:s,ctx:t}),e.$set(a)},i(t){s||(l(e.$$.fragment,t),s=!0)},o(t){c(e.$$.fragment,t),s=!1},d(t){u(e,t)}}}function I(t,e,s){var a=this&&this.__awaiter||function(t,e,s,a){return new(s||(s=Promise))((function(n,i){function l(t){try{u(a.next(t))}catch(t){i(t)}}function c(t){try{u(a.throw(t))}catch(t){i(t)}}function u(t){var e;t.done?n(t.value):(e=t.value,e instanceof s?e:new s((function(t){t(e)}))).then(l,c)}u((a=a.apply(t,e||[])).next())}))};const n=o();let i=null,l="",c=!1,u="",f=!1;r((function(){return a(this,void 0,void 0,(function*(){s(1,l=""),s(3,u=""),yield d(),i.focus()}))}));return[i,l,c,u,f,n,function(){return a(this,void 0,void 0,(function*(){s(2,c=!0);const t=yield P("/album/new",{params:{title:l,isPrivate:f},method:"POST",authorize:!0});t.success?n("done",{newAlbumID:t.data.albumID}):s(3,u="Duplicate name!"),s(2,c=!1)}))},function(t){A[t?"unshift":"push"]((()=>{i=t,s(0,i)}))},function(){l=this.value,s(1,l)},function(){f=this.checked,s(4,f)},()=>n("dismissed")]}export default class extends t{constructor(t){super(),e(this,t,I,z,s,{})}}