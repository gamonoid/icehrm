/**
 * Skipped minification because the original files appears to be already minified.
 * Original file: /npm/@editorjs/delimiter@1.4.2/dist/delimiter.umd.js
 *
 * Do NOT use SRI with dynamically generated files! More information: https://www.jsdelivr.com/using-sri-with-dynamic-files
 */
(function(){"use strict";try{if(typeof document<"u"){var e=document.createElement("style");e.appendChild(document.createTextNode('.ce-delimiter{line-height:1.6em;width:100%;text-align:center}.ce-delimiter:before{display:inline-block;content:"***";font-size:30px;line-height:65px;height:30px;letter-spacing:.2em}')),document.head.appendChild(e)}}catch(t){console.error("vite-plugin-css-injected-by-js",t)}})();
(function(t,i){typeof exports=="object"&&typeof module<"u"?module.exports=i():typeof define=="function"&&define.amd?define(i):(t=typeof globalThis<"u"?globalThis:t||self,t.Delimiter=i())})(this,function(){"use strict";const t='<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><line x1="6" x2="10" y1="12" y2="12" stroke="currentColor" stroke-linecap="round" stroke-width="2"/><line x1="14" x2="18" y1="12" y2="12" stroke="currentColor" stroke-linecap="round" stroke-width="2"/></svg>';/**
 * Delimiter Block for the Editor.js.
 *
 * @author CodeX (team@ifmo.su)
 * @copyright CodeX 2018
 * @license The MIT License (MIT)
 * @version 2.0.0
 */class i{static get isReadOnlySupported(){return!0}static get contentless(){return!0}constructor({data:e,config:s,api:r}){this.api=r,this._CSS={block:this.api.styles.block,wrapper:"ce-delimiter"},this._element=this.drawView(),this.data=e}drawView(){let e=document.createElement("div");return e.classList.add(this._CSS.wrapper,this._CSS.block),e}render(){return this._element}save(e){return{}}static get toolbox(){return{icon:t,title:"Delimiter"}}static get pasteConfig(){return{tags:["HR"]}}onPaste(e){this.data={}}}return i});
