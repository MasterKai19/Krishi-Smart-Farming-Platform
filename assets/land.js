const currency = n => 'BDT ' + new Intl.NumberFormat('en-BD', {minimumFractionDigits:2,maximumFractionDigits:2}).format(n);
const q = document.getElementById('cost-quantity'), r = document.getElementById('cost-rate');
if(q && r){const calc=()=>{const total=Number(q.value)*Number(r.value);document.getElementById('cost-result').textContent=Number.isFinite(total)&&total>=0?currency(total):'Enter valid positive values';};q.addEventListener('input',calc);r.addEventListener('input',calc);calc();}
const estimates=[...document.querySelectorAll('.estimate-amount')];
estimates.forEach(el=>el.addEventListener('input',()=>{document.getElementById('estimate-result').textContent=currency(estimates.reduce((sum,input)=>sum+Math.max(0,Number(input.value)||0),0));}));
const landSearch=document.getElementById('land-search');
if(landSearch)landSearch.addEventListener('input',()=>{let visible=0;document.querySelectorAll('[data-land-search]').forEach(el=>{el.hidden=!el.dataset.landSearch.includes(landSearch.value.toLowerCase().trim());if(!el.hidden)visible++;});document.getElementById('land-no-match').hidden=visible>0;});
