import{P as e,S as t,g as n,k as r,m as i,n as a,t as o,x as s,y as c,z as l}from"./app-B3wKtsCg.js";import{t as u}from"./_plugin-vue_export-helper-B80Cc4Ui.js";import{t as d}from"./AuthenticatedLayout-vHeVj6aG.js";var f={class:`home-hero`},p={class:`home-grid`},m={class:`hero-copy`},h={class:`hero-actions`},g=u({__name:`Home`,setup(u){return(u,g)=>(r(),c(i,null,[t(l(o),{title:`Home`}),t(d,null,{default:e(()=>[n(`section`,f,[n(`div`,p,[n(`div`,m,[g[2]||=n(`h1`,null,`Practice algorithms inside a sharper AlgoByte workspace.`,-1),g[3]||=n(`p`,null,` Write solutions, shape test cases, and send code to the execution engine from one dark, focused interface inspired by the Figma design. `,-1),n(`div`,h,[t(l(a),{href:u.route(`browse-problems.index`),class:`primary-action`},{default:e(()=>[...g[0]||=[s(` Start Coding `,-1)]]),_:1},8,[`href`]),t(l(a),{href:u.route(`problem-creation.index`),class:`secondary-action`},{default:e(()=>[...g[1]||=[s(` Create A Problem ! `,-1)]]),_:1},8,[`href`])])]),g[4]||=n(`div`,{class:`terminal-panel`},[n(`div`,{class:`terminal-top`},[n(`span`),n(`span`),n(`span`),n(`strong`,null,`binary-search.ts`)]),n(`pre`,null,[n(`code`,null,`function binarySearch(items, target) {
  let left = 0
  let right = items.length - 1

  while (left <= right) {
    const mid = Math.floor((left + right) / 2)
    if (items[mid] === target) return mid
    if (items[mid] < target) left = mid + 1
    else right = mid - 1
  }

  return -1
}`)]),n(`div`,{class:`status-strip`},[n(`span`,null,`Tests: 4 passed`),n(`span`,null,`Runtime: 42ms`)])],-1)])])]),_:1})],64))}},[[`__scopeId`,`data-v-026d9023`]]);export{g as default};