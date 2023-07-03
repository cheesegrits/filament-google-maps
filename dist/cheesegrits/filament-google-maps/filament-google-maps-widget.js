(()=>{"use strict";var t={63:t=>{t.exports=function t(e,r){if(e===r)return!0;if(e&&r&&"object"==typeof e&&"object"==typeof r){if(e.constructor!==r.constructor)return!1;var n,s,i;if(Array.isArray(e)){if((n=e.length)!=r.length)return!1;for(s=n;0!=s--;)if(!t(e[s],r[s]))return!1;return!0}if(e.constructor===RegExp)return e.source===r.source&&e.flags===r.flags;if(e.valueOf!==Object.prototype.valueOf)return e.valueOf()===r.valueOf();if(e.toString!==Object.prototype.toString)return e.toString()===r.toString();if((n=(i=Object.keys(e)).length)!==Object.keys(r).length)return!1;for(s=n;0!=s--;)if(!Object.prototype.hasOwnProperty.call(r,i[s]))return!1;for(s=n;0!=s--;){var o=i[s];if(!t(e[o],r[o]))return!1}return!0}return e!=e&&r!=r}}},e={};function r(n){var s=e[n];if(void 0!==s)return s.exports;var i=e[n]={exports:{}};return t[n](i,i.exports,r),i.exports}r.n=t=>{var e=t&&t.__esModule?()=>t.default:()=>t;return r.d(e,{a:e}),e},r.d=(t,e)=>{for(var n in e)r.o(e,n)&&!r.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:e[n]})},r.o=(t,e)=>Object.prototype.hasOwnProperty.call(t,e),(()=>{var t=r(63),e=r.n(t);const n=[Int8Array,Uint8Array,Uint8ClampedArray,Int16Array,Uint16Array,Int32Array,Uint32Array,Float32Array,Float64Array];class s{static from(t){if(!(t instanceof ArrayBuffer))throw new Error("Data must be an instance of ArrayBuffer.");const[e,r]=new Uint8Array(t,0,2);if(219!==e)throw new Error("Data does not appear to be in a KDBush format.");const i=r>>4;if(1!==i)throw new Error(`Got v${i} data when expected v1.`);const o=n[15&r];if(!o)throw new Error("Unrecognized array type.");const[a]=new Uint16Array(t,2,1),[l]=new Uint32Array(t,4,1);return new s(l,a,o,t)}constructor(t,e=64,r=Float64Array,s){if(isNaN(t)||t<0)throw new Error(`Unpexpected numItems value: ${t}.`);this.numItems=+t,this.nodeSize=Math.min(Math.max(+e,2),65535),this.ArrayType=r,this.IndexArrayType=t<65536?Uint16Array:Uint32Array;const i=n.indexOf(this.ArrayType),o=2*t*this.ArrayType.BYTES_PER_ELEMENT,a=t*this.IndexArrayType.BYTES_PER_ELEMENT,l=(8-a%8)%8;if(i<0)throw new Error(`Unexpected typed array class: ${r}.`);s&&s instanceof ArrayBuffer?(this.data=s,this.ids=new this.IndexArrayType(this.data,8,t),this.coords=new this.ArrayType(this.data,8+a+l,2*t),this._pos=2*t,this._finished=!0):(this.data=new ArrayBuffer(8+o+a+l),this.ids=new this.IndexArrayType(this.data,8,t),this.coords=new this.ArrayType(this.data,8+a+l,2*t),this._pos=0,this._finished=!1,new Uint8Array(this.data,0,2).set([219,16+i]),new Uint16Array(this.data,2,1)[0]=e,new Uint32Array(this.data,4,1)[0]=t)}add(t,e){const r=this._pos>>1;return this.ids[r]=r,this.coords[this._pos++]=t,this.coords[this._pos++]=e,r}finish(){const t=this._pos>>1;if(t!==this.numItems)throw new Error(`Added ${t} items when expected ${this.numItems}.`);return i(this.ids,this.coords,this.nodeSize,0,this.numItems-1,0),this._finished=!0,this}range(t,e,r,n){if(!this._finished)throw new Error("Data not yet indexed - call index.finish().");const{ids:s,coords:i,nodeSize:o}=this,a=[0,s.length-1,0],l=[];for(;a.length;){const c=a.pop()||0,h=a.pop()||0,u=a.pop()||0;if(h-u<=o){for(let o=u;o<=h;o++){const a=i[2*o],c=i[2*o+1];a>=t&&a<=r&&c>=e&&c<=n&&l.push(s[o])}continue}const p=u+h>>1,m=i[2*p],d=i[2*p+1];m>=t&&m<=r&&d>=e&&d<=n&&l.push(s[p]),(0===c?t<=m:e<=d)&&(a.push(u),a.push(p-1),a.push(1-c)),(0===c?r>=m:n>=d)&&(a.push(p+1),a.push(h),a.push(1-c))}return l}within(t,e,r){if(!this._finished)throw new Error("Data not yet indexed - call index.finish().");const{ids:n,coords:s,nodeSize:i}=this,o=[0,n.length-1,0],a=[],l=r*r;for(;o.length;){const h=o.pop()||0,u=o.pop()||0,p=o.pop()||0;if(u-p<=i){for(let r=p;r<=u;r++)c(s[2*r],s[2*r+1],t,e)<=l&&a.push(n[r]);continue}const m=p+u>>1,d=s[2*m],f=s[2*m+1];c(d,f,t,e)<=l&&a.push(n[m]),(0===h?t-r<=d:e-r<=f)&&(o.push(p),o.push(m-1),o.push(1-h)),(0===h?t+r>=d:e+r>=f)&&(o.push(m+1),o.push(u),o.push(1-h))}return a}}function i(t,e,r,n,s,a){if(s-n<=r)return;const l=n+s>>1;o(t,e,l,n,s,a),i(t,e,r,n,l-1,1-a),i(t,e,r,l+1,s,1-a)}function o(t,e,r,n,s,i){for(;s>n;){if(s-n>600){const a=s-n+1,l=r-n+1,c=Math.log(a),h=.5*Math.exp(2*c/3),u=.5*Math.sqrt(c*h*(a-h)/a)*(l-a/2<0?-1:1);o(t,e,r,Math.max(n,Math.floor(r-l*h/a+u)),Math.min(s,Math.floor(r+(a-l)*h/a+u)),i)}const l=e[2*r+i];let c=n,h=s;for(a(t,e,n,r),e[2*s+i]>l&&a(t,e,n,s);c<h;){for(a(t,e,c,h),c++,h--;e[2*c+i]<l;)c++;for(;e[2*h+i]>l;)h--}e[2*n+i]===l?a(t,e,n,h):(h++,a(t,e,h,s)),h<=r&&(n=h+1),r<=h&&(s=h-1)}}function a(t,e,r,n){l(t,r,n),l(e,2*r,2*n),l(e,2*r+1,2*n+1)}function l(t,e,r){const n=t[e];t[e]=t[r],t[r]=n}function c(t,e,r,n){const s=t-r,i=e-n;return s*s+i*i}const h={minZoom:0,maxZoom:16,minPoints:2,radius:40,extent:512,nodeSize:64,log:!1,generateId:!1,reduce:null,map:t=>t},u=Math.fround||(p=new Float32Array(1),t=>(p[0]=+t,p[0]));var p;const m=3,d=5,f=6;class g{constructor(t){this.options=Object.assign(Object.create(h),t),this.trees=new Array(this.options.maxZoom+1),this.stride=this.options.reduce?7:6,this.clusterProps=[]}load(t){const{log:e,minZoom:r,maxZoom:n}=this.options;e&&console.time("total time");const s=`prepare ${t.length} points`;e&&console.time(s),this.points=t;const i=[];for(let e=0;e<t.length;e++){const r=t[e];if(!r.geometry)continue;const[n,s]=r.geometry.coordinates,o=u(v(n)),a=u(k(s));i.push(o,a,1/0,e,-1,1),this.options.reduce&&i.push(0)}let o=this.trees[n+1]=this._createTree(i);e&&console.timeEnd(s);for(let t=n;t>=r;t--){const r=+Date.now();o=this.trees[t]=this._createTree(this._cluster(o,t)),e&&console.log("z%d: %d clusters in %dms",t,o.numItems,+Date.now()-r)}return e&&console.timeEnd("total time"),this}getClusters(t,e){let r=((t[0]+180)%360+360)%360-180;const n=Math.max(-90,Math.min(90,t[1]));let s=180===t[2]?180:((t[2]+180)%360+360)%360-180;const i=Math.max(-90,Math.min(90,t[3]));if(t[2]-t[0]>=360)r=-180,s=180;else if(r>s){const t=this.getClusters([r,n,180,i],e),o=this.getClusters([-180,n,s,i],e);return t.concat(o)}const o=this.trees[this._limitZoom(e)],a=o.range(v(r),k(i),v(s),k(n)),l=o.data,c=[];for(const t of a){const e=this.stride*t;c.push(l[e+d]>1?y(l,e,this.clusterProps):this.points[l[e+m]])}return c}getChildren(t){const e=this._getOriginId(t),r=this._getOriginZoom(t),n="No cluster with the specified id.",s=this.trees[r];if(!s)throw new Error(n);const i=s.data;if(e*this.stride>=i.length)throw new Error(n);const o=this.options.radius/(this.options.extent*Math.pow(2,r-1)),a=i[e*this.stride],l=i[e*this.stride+1],c=s.within(a,l,o),h=[];for(const e of c){const r=e*this.stride;i[r+4]===t&&h.push(i[r+d]>1?y(i,r,this.clusterProps):this.points[i[r+m]])}if(0===h.length)throw new Error(n);return h}getLeaves(t,e,r){e=e||10,r=r||0;const n=[];return this._appendLeaves(n,t,e,r,0),n}getTile(t,e,r){const n=this.trees[this._limitZoom(t)],s=Math.pow(2,t),{extent:i,radius:o}=this.options,a=o/i,l=(r-a)/s,c=(r+1+a)/s,h={features:[]};return this._addTileFeatures(n.range((e-a)/s,l,(e+1+a)/s,c),n.data,e,r,s,h),0===e&&this._addTileFeatures(n.range(1-a/s,l,1,c),n.data,s,r,s,h),e===s-1&&this._addTileFeatures(n.range(0,l,a/s,c),n.data,-1,r,s,h),h.features.length?h:null}getClusterExpansionZoom(t){let e=this._getOriginZoom(t)-1;for(;e<=this.options.maxZoom;){const r=this.getChildren(t);if(e++,1!==r.length)break;t=r[0].properties.cluster_id}return e}_appendLeaves(t,e,r,n,s){const i=this.getChildren(e);for(const e of i){const i=e.properties;if(i&&i.cluster?s+i.point_count<=n?s+=i.point_count:s=this._appendLeaves(t,i.cluster_id,r,n,s):s<n?s++:t.push(e),t.length===r)break}return s}_createTree(t){const e=new s(t.length/this.stride|0,this.options.nodeSize,Float32Array);for(let r=0;r<t.length;r+=this.stride)e.add(t[r],t[r+1]);return e.finish(),e.data=t,e}_addTileFeatures(t,e,r,n,s,i){for(const o of t){const t=o*this.stride,a=e[t+d]>1;let l,c,h;if(a)l=w(e,t,this.clusterProps),c=e[t],h=e[t+1];else{const r=this.points[e[t+m]];l=r.properties;const[n,s]=r.geometry.coordinates;c=v(n),h=k(s)}const u={type:1,geometry:[[Math.round(this.options.extent*(c*s-r)),Math.round(this.options.extent*(h*s-n))]],tags:l};let p;p=a||this.options.generateId?e[t+m]:this.points[e[t+m]].id,void 0!==p&&(u.id=p),i.features.push(u)}}_limitZoom(t){return Math.max(this.options.minZoom,Math.min(Math.floor(+t),this.options.maxZoom+1))}_cluster(t,e){const{radius:r,extent:n,reduce:s,minPoints:i}=this.options,o=r/(n*Math.pow(2,e)),a=t.data,l=[],c=this.stride;for(let r=0;r<a.length;r+=c){if(a[r+2]<=e)continue;a[r+2]=e;const n=a[r],h=a[r+1],u=t.within(a[r],a[r+1],o),p=a[r+d];let m=p;for(const t of u){const r=t*c;a[r+2]>e&&(m+=a[r+d])}if(m>p&&m>=i){let t,i=n*p,o=h*p,f=-1;const g=((r/c|0)<<5)+(e+1)+this.points.length;for(const n of u){const l=n*c;if(a[l+2]<=e)continue;a[l+2]=e;const h=a[l+d];i+=a[l]*h,o+=a[l+1]*h,a[l+4]=g,s&&(t||(t=this._map(a,r,!0),f=this.clusterProps.length,this.clusterProps.push(t)),s(t,this._map(a,l)))}a[r+4]=g,l.push(i/m,o/m,1/0,g,-1,m),s&&l.push(f)}else{for(let t=0;t<c;t++)l.push(a[r+t]);if(m>1)for(const t of u){const r=t*c;if(!(a[r+2]<=e)){a[r+2]=e;for(let t=0;t<c;t++)l.push(a[r+t])}}}}return l}_getOriginId(t){return t-this.points.length>>5}_getOriginZoom(t){return(t-this.points.length)%32}_map(t,e,r){if(t[e+d]>1){const n=this.clusterProps[t[e+f]];return r?Object.assign({},n):n}const n=this.points[t[e+m]].properties,s=this.options.map(n);return r&&s===n?Object.assign({},s):s}}function y(t,e,r){return{type:"Feature",id:t[e+m],properties:w(t,e,r),geometry:{type:"Point",coordinates:[(n=t[e],360*(n-.5)),M(t[e+1])]}};var n}function w(t,e,r){const n=t[e+d],s=n>=1e4?`${Math.round(n/1e3)}k`:n>=1e3?Math.round(n/100)/10+"k":n,i=t[e+f],o=-1===i?{}:Object.assign({},r[i]);return Object.assign(o,{cluster:!0,cluster_id:t[e+m],point_count:n,point_count_abbreviated:s})}function v(t){return t/360+.5}function k(t){const e=Math.sin(t*Math.PI/180),r=.5-.25*Math.log((1+e)/(1-e))/Math.PI;return r<0?0:r>1?1:r}function M(t){const e=(180-360*t)*Math.PI/180;return 360*Math.atan(Math.exp(e))/Math.PI-90}
/*! *****************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */
function b(t,e){var r={};for(var n in t)Object.prototype.hasOwnProperty.call(t,n)&&e.indexOf(n)<0&&(r[n]=t[n]);if(null!=t&&"function"==typeof Object.getOwnPropertySymbols){var s=0;for(n=Object.getOwnPropertySymbols(t);s<n.length;s++)e.indexOf(n[s])<0&&Object.prototype.propertyIsEnumerable.call(t,n[s])&&(r[n[s]]=t[n[s]])}return r}class x{static isAdvancedMarkerAvailable(t){return google.maps.marker&&!0===t.getMapCapabilities().isAdvancedMarkersAvailable}static isAdvancedMarker(t){return google.maps.marker&&t instanceof google.maps.marker.AdvancedMarkerElement}static setMap(t,e){this.isAdvancedMarker(t)?t.map=e:t.setMap(e)}static getPosition(t){if(this.isAdvancedMarker(t)){if(t.position){if(t.position instanceof google.maps.LatLng)return t.position;if(t.position.lat&&t.position.lng)return new google.maps.LatLng(t.position.lat,t.position.lng)}return new google.maps.LatLng(null)}return t.getPosition()}static getVisible(t){return!!this.isAdvancedMarker(t)||t.getVisible()}}class A{constructor({markers:t,position:e}){this.markers=t,e&&(e instanceof google.maps.LatLng?this._position=e:this._position=new google.maps.LatLng(e))}get bounds(){if(0===this.markers.length&&!this._position)return;const t=new google.maps.LatLngBounds(this._position,this._position);for(const e of this.markers)t.extend(x.getPosition(e));return t}get position(){return this._position||this.bounds.getCenter()}get count(){return this.markers.filter((t=>x.getVisible(t))).length}push(t){this.markers.push(t)}delete(){this.marker&&(x.setMap(this.marker,null),this.marker=void 0),this.markers.length=0}}class _{constructor({maxZoom:t=16}){this.maxZoom=t}noop({markers:t}){return O(t)}}const O=t=>t.map((t=>new A({position:x.getPosition(t),markers:[t]})));class P extends _{constructor(t){var{maxZoom:e,radius:r=60}=t,n=b(t,["maxZoom","radius"]);super({maxZoom:e}),this.state={zoom:-1},this.superCluster=new g(Object.assign({maxZoom:this.maxZoom,radius:r},n))}calculate(t){let r=!1;const n={zoom:t.map.getZoom()};if(!e()(t.markers,this.markers)){r=!0,this.markers=[...t.markers];const e=this.markers.map((t=>{const e=x.getPosition(t);return{type:"Feature",geometry:{type:"Point",coordinates:[e.lng(),e.lat()]},properties:{marker:t}}}));this.superCluster.load(e)}return r||(this.state.zoom<=this.maxZoom||n.zoom<=this.maxZoom)&&(r=!e()(this.state,n)),this.state=n,r&&(this.clusters=this.cluster(t)),{clusters:this.clusters,changed:r}}cluster({map:t}){return this.superCluster.getClusters([-180,-90,180,90],Math.round(t.getZoom())).map((t=>this.transformCluster(t)))}transformCluster({geometry:{coordinates:[t,e]},properties:r}){if(r.cluster)return new A({markers:this.superCluster.getLeaves(r.cluster_id,1/0).map((t=>t.properties.marker)),position:{lat:e,lng:t}});const n=r.marker;return new A({markers:[n],position:x.getPosition(n)})}}class E{constructor(t,e){this.markers={sum:t.length};const r=e.map((t=>t.count)),n=r.reduce(((t,e)=>t+e),0);this.clusters={count:e.length,markers:{mean:n/e.length,sum:n,min:Math.min(...r),max:Math.max(...r)}}}}class C{render({count:t,position:e},r,n){const s=`<svg fill="${t>Math.max(10,r.clusters.markers.mean)?"#ff0000":"#0000ff"}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240" width="50" height="50">\n<circle cx="120" cy="120" opacity=".6" r="70" />\n<circle cx="120" cy="120" opacity=".3" r="90" />\n<circle cx="120" cy="120" opacity=".2" r="110" />\n<text x="50%" y="50%" style="fill:#fff" text-anchor="middle" font-size="50" dominant-baseline="middle" font-family="roboto,arial,sans-serif">${t}</text>\n</svg>`,i=`Cluster of ${t} markers`,o=Number(google.maps.Marker.MAX_ZINDEX)+t;if(x.isAdvancedMarkerAvailable(n)){const t=document.createElement("div");t.innerHTML=s;const r=t.firstElementChild;r.setAttribute("transform","translate(0 25)");const a={map:n,position:e,zIndex:o,title:i,content:r};return new google.maps.marker.AdvancedMarkerElement(a)}const a={position:e,zIndex:o,title:i,icon:{url:`data:image/svg+xml;base64,${btoa(s)}`,anchor:new google.maps.Point(25,25)}};return new google.maps.Marker(a)}}class I{constructor(){!function(t,e){for(let r in e.prototype)t.prototype[r]=e.prototype[r]}(I,google.maps.OverlayView)}}var L;!function(t){t.CLUSTERING_BEGIN="clusteringbegin",t.CLUSTERING_END="clusteringend",t.CLUSTER_CLICK="click"}(L||(L={}));const j=(t,e,r)=>{r.fitBounds(e.bounds)};class S extends I{constructor({map:t,markers:e=[],algorithmOptions:r={},algorithm:n=new P(r),renderer:s=new C,onClusterClick:i=j}){super(),this.markers=[...e],this.clusters=[],this.algorithm=n,this.renderer=s,this.onClusterClick=i,t&&this.setMap(t)}addMarker(t,e){this.markers.includes(t)||(this.markers.push(t),e||this.render())}addMarkers(t,e){t.forEach((t=>{this.addMarker(t,!0)})),e||this.render()}removeMarker(t,e){const r=this.markers.indexOf(t);return-1!==r&&(x.setMap(t,null),this.markers.splice(r,1),e||this.render(),!0)}removeMarkers(t,e){let r=!1;return t.forEach((t=>{r=this.removeMarker(t,!0)||r})),r&&!e&&this.render(),r}clearMarkers(t){this.markers.length=0,t||this.render()}render(){const t=this.getMap();if(t instanceof google.maps.Map&&t.getProjection()){google.maps.event.trigger(this,L.CLUSTERING_BEGIN,this);const{clusters:e,changed:r}=this.algorithm.calculate({markers:this.markers,map:t,mapCanvasProjection:this.getProjection()});(r||null==r)&&(this.reset(),this.clusters=e,this.renderClusters()),google.maps.event.trigger(this,L.CLUSTERING_END,this)}}onAdd(){this.idleListener=this.getMap().addListener("idle",this.render.bind(this)),this.render()}onRemove(){google.maps.event.removeListener(this.idleListener),this.reset()}reset(){this.markers.forEach((t=>x.setMap(t,null))),this.clusters.forEach((t=>t.delete())),this.clusters=[]}renderClusters(){const t=new E(this.markers,this.clusters),e=this.getMap();this.clusters.forEach((r=>{1===r.markers.length?r.marker=r.markers[0]:(r.marker=this.renderer.render(r,t,e),this.onClusterClick&&r.marker.addListener("click",(t=>{google.maps.event.trigger(this,L.CLUSTER_CLICK,r),this.onClusterClick(t,r,e)}))),x.setMap(r.marker,e)}))}}const T=Date.now||function(){return(new Date).getTime()};function Z(t,e,r){var n,s,i,o,a,l=function(){var c=T()-s;e>c?n=setTimeout(l,e-c):(n=null,r||(o=t.apply(a,i)),n||(i=a=null))},c=function(t,e){return e=null==e?t.length-1:+e,function(){for(var r=Math.max(arguments.length-e,0),n=Array(r),s=0;s<r;s++)n[s]=arguments[s+e];switch(e){case 0:return t.call(this,n);case 1:return t.call(this,arguments[0],n);case 2:return t.call(this,arguments[0],arguments[1],n)}var i=Array(e+1);for(s=0;s<e;s++)i[s]=arguments[s];return i[e]=n,t.apply(this,i)}}((function(c){return a=this,i=c,s=T(),n||(n=setTimeout(l,e),r&&(o=t.apply(a,i))),o}));return c.cancel=function(){clearTimeout(n),n=i=a=null},c}function z(t,e){var r="undefined"!=typeof Symbol&&t[Symbol.iterator]||t["@@iterator"];if(!r){if(Array.isArray(t)||(r=function(t,e){if(!t)return;if("string"==typeof t)return U(t,e);var r=Object.prototype.toString.call(t).slice(8,-1);"Object"===r&&t.constructor&&(r=t.constructor.name);if("Map"===r||"Set"===r)return Array.from(t);if("Arguments"===r||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(r))return U(t,e)}(t))||e&&t&&"number"==typeof t.length){r&&(t=r);var n=0,s=function(){};return{s,n:function(){return n>=t.length?{done:!0}:{done:!1,value:t[n++]}},e:function(t){throw t},f:s}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var i,o=!0,a=!1;return{s:function(){r=r.call(t)},n:function(){var t=r.next();return o=t.done,t},e:function(t){a=!0,i=t},f:function(){try{o||null==r.return||r.return()}finally{if(a)throw i}}}}function U(t,e){(null==e||e>t.length)&&(e=t.length);for(var r=0,n=new Array(e);r<e;r++)n[r]=t[r];return n}function B(t){return B="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},B(t)}function D(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function F(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?D(Object(r),!0).forEach((function(e){N(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):D(Object(r)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}function N(t,e,r){return(e=function(t){var e=function(t,e){if("object"!==B(t)||null===t)return t;var r=t[Symbol.toPrimitive];if(void 0!==r){var n=r.call(t,e||"default");if("object"!==B(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===e?String:Number)(t)}(t,"string");return"symbol"===B(e)?e:String(e)}(e))in t?Object.defineProperty(t,e,{value:r,enumerable:!0,configurable:!0,writable:!0}):t[e]=r,t}window.filamentGoogleMapsWidget=function(t,e){return{wire:null,map:null,bounds:null,infoWindow:null,mapEl:null,data:null,markers:[],layers:[],modelIds:[],mapIsFilter:!1,clusterer:null,center:null,isMapDragging:!1,isIdleSkipped:!1,config:{center:{lat:0,lng:0},clustering:!1,controls:{mapTypeControl:!0,scaleControl:!0,streetViewControl:!0,rotateControl:!0,fullscreenControl:!0,searchBoxControl:!1,zoomControl:!1},fit:!0,mapIsFilter:!1,gmaps:"",layers:[],zoom:12},loadGMaps:function(){if(document.getElementById("filament-google-maps-google-maps-js")){!function t(e,r){window[e]?r():setTimeout((function(){t(e,r)}),100)}("filamentGoogleMapsAPILoaded",function(){this.createMap()}.bind(this))}else{var t=document.createElement("script");t.id="filament-google-maps-google-maps-js",window.filamentGoogleMapsAsyncLoad=this.createMap.bind(this),t.src=this.config.gmaps+"&callback=filamentGoogleMapsAsyncLoad",document.head.appendChild(t)}},init:function(r,n){this.mapEl=document.getElementById(n)||n,this.data=r,this.wire=t,this.config=F(F({},this.config),e),this.loadGMaps()},callWire:function(t){},createMap:function(){window.filamentGoogleMapsAPILoaded=!0,this.infoWindow=new google.maps.InfoWindow({content:"",disableAutoPan:!0}),this.map=new google.maps.Map(this.mapEl,F({center:this.config.center,zoom:this.config.zoom},this.config.controls)),this.center=this.config.center,this.createMarkers(),this.createClustering(),this.createLayers(),this.idle(),this.show(!0)},show:function(){var t=arguments.length>0&&void 0!==arguments[0]&&arguments[0];this.config.fit?this.fitToBounds(t):this.markers.length>0&&this.map.setCenter(this.markers[0].getPosition())},createLayers:function(){var t=this;this.layers=this.config.layers.map((function(e){return new google.maps.KmlLayer({url:e,map:t.map})}))},createMarker:function(t){var e;t.icon&&"object"===B(t.icon)&&t.icon.hasOwnProperty("url")&&(e={url:t.icon.url},t.icon.hasOwnProperty("type")&&"svg"===t.icon.type&&t.icon.hasOwnProperty("scale")&&(e.scaledSize=new google.maps.Size(t.icon.scale[0],t.icon.scale[1])));var r=t.location,n=t.label,s=new google.maps.Marker(F({position:r,title:n,model_id:t.id},e&&{icon:e}));return-1===this.modelIds.indexOf(t.id)&&this.modelIds.push(t.id),s},createMarkers:function(){var t=this,e=this;this.markers=this.data.map((function(r){var n=t.createMarker(r);n.setMap(t.map);return google.maps.event.addListener(n,"click",(function(t){e.wire.mountTableAction("edit",n.model_id)})),n}))},removeMarker:function(t){t.setMap(null)},removeMarkers:function(){for(var t=0;t<this.markers.length;t++)this.markers[t].setMap(null);this.markers=[]},mergeMarkers:function(){var t=this,e=function(t,e){var r=arguments.length>2&&void 0!==arguments[2]&&arguments[2];return t.filter((function(t){return r===e.some((function(e){return t.getPosition().lat()===e.getPosition().lat()&&t.getPosition().lng()===e.getPosition().lng()}))}))},r=e,n=function(t,e){return r(e,t)},s=this.data.map((function(e){var r=t.createMarker(e);return r.addListener("click",(function(){t.infoWindow.setContent(e.label),t.infoWindow.open(t.map,r)})),r}));if(!this.config.mapIsFilter)for(var i=n(s,this.markers),o=function(e){i[e].setMap(null);var r=t.markers.findIndex((function(t){return t.getPosition().lat()===i[e].getPosition().lat()&&t.getPosition().lng()===i[e].getPosition().lng()}));t.markers.splice(r,1)},a=i.length-1;a>=0;a--)o(a);for(var l=n(this.markers,s),c=0;c<l.length;c++)l[c].setMap(this.map),this.markers.push(l[c]);this.fitToBounds()},fitToBounds:function(){var t=arguments.length>0&&void 0!==arguments[0]&&arguments[0];if(this.config.fit&&(t||!this.config.mapIsFilter)){this.bounds=new google.maps.LatLngBounds;var e,r=z(this.markers);try{for(r.s();!(e=r.n()).done;){var n=e.value;this.bounds.extend(n.getPosition())}}catch(t){r.e(t)}finally{r.f()}this.map.fitBounds(this.bounds)}},createClustering:function(){this.config.clustering&&(this.clusterer=new S({map:this.map,markers:this.markers}))},updateClustering:function(){this.config.clustering&&(this.clusterer.clearMarkers(),this.clusterer.addMarkers(this.markers))},moved:function(){console.log("moved");var e,r,n=this.map.getBounds(),s=this.markers.filter((function(t){return n.contains(t.getPosition())})).map((function(t){return t.model_id}));e=this.modelIds,r=s,e.length===r.length&&e.every((function(t,e){return t===r[e]}))||(this.modelIds=s,console.log(s),t.set("mapFilterIds",s))},idle:function(){if(this.config.mapIsFilter){self;var t=Z(this.moved,1e3).bind(this);google.maps.event.addListener(this.map,"idle",(function(e){self.isMapDragging?self.idleSkipped=!0:(self.idleSkipped=!1,t())})),google.maps.event.addListener(this.map,"dragstart",(function(t){self.isMapDragging=!0})),google.maps.event.addListener(this.map,"dragend",(function(e){self.isMapDragging=!1,!0===self.idleSkipped&&(t(),self.idleSkipped=!1)})),google.maps.event.addListener(this.map,"bounds_changed",(function(t){self.idleSkipped=!1}))}},update:function(t){this.data=t,this.mergeMarkers(),this.updateClustering(),this.show()},recenter:function(t){this.map.panTo({lat:t.lat,lng:t.lng}),this.map.setZoom(t.zoom)}}}})()})();
//# sourceMappingURL=filament-google-maps-widget.js.map