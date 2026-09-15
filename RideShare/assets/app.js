const C=window.RIDE_CONFIG;
let map, userMarker, routeLine, driverMarker, currentPos={lat:7.1193,lng:-73.1227}, selectedType=C?.vehiculo||'carro', selectedPay='efectivo', activeRide=null, poller=null;
const $=s=>document.querySelector(s), $$=s=>document.querySelectorAll(s);
const money=n=>new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',maximumFractionDigits:0}).format(n);

function initMap(){
 map=L.map('map').setView([currentPos.lat,currentPos.lng],14);
 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'© OpenStreetMap'}).addTo(map);
 userMarker=L.marker([currentPos.lat,currentPos.lng],{icon:L.divIcon({className:'pin user-pin',html:'📍',iconSize:[34,34]})}).addTo(map);
 const applyPosition=async p=>{
   currentPos={lat:p.coords.latitude,lng:p.coords.longitude};
   userMarker.setLatLng([currentPos.lat,currentPos.lng]);
   map.setView([currentPos.lat,currentPos.lng],15);
   const origin=$('#origin');
   if(origin) origin.value=await reverse(currentPos);
   const d=$('#destination')?.dataset;
   if(d?.lat&&d?.lng) drawRoute(currentPos,{lat:+d.lat,lng:+d.lng});
 };
 if(navigator.geolocation){
   navigator.geolocation.getCurrentPosition(applyPosition,()=>{if($('#origin'))$('#origin').value='Mi ubicación actual';},{enableHighAccuracy:true,timeout:10000,maximumAge:0});
   navigator.geolocation.watchPosition(applyPosition,()=>{}, {enableHighAccuracy:true,maximumAge:5000,timeout:15000});
 } else if($('#origin')) $('#origin').value='Mi ubicación actual';
}
async function reverse(p){try{let r=await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${p.lat}&lon=${p.lng}`);let j=await r.json();return j.display_name||'Mi ubicación actual'}catch{return 'Mi ubicación actual'}}
async function geocode(q){
 if(!q) return [];
 try{
   const query=/santander|colombia/i.test(q)?q:q+', Santander, Colombia';
   const url=`https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=10&countrycodes=co&viewbox=-74.6,8.1,-71.9,5.4&bounded=1&q=${encodeURIComponent(query)}`;
   const r=await fetch(url,{headers:{'Accept-Language':'es','Accept':'application/json'}});
   const a=await r.json();
   return a.filter(x=>{
     const ad=x.address||{};
     const text=(x.display_name||'').toLowerCase();
     const country=(ad.country_code||'').toLowerCase();
     const state=(ad.state||'').toLowerCase();
     return country==='co' && (/santander/.test(state)||/santander/.test(text));
   });
 }catch{return []}
}

async function categoryPlaces(query){ return geocode(query); }
async function drawRoute(a,b){
 try{let r=await fetch(`https://router.project-osrm.org/route/v1/driving/${a.lng},${a.lat};${b.lng},${b.lat}?overview=full&geometries=geojson`);
 let j=await r.json(); if(!j.routes?.[0])return null; let rt=j.routes[0]; if(routeLine)map.removeLayer(routeLine);
 routeLine=L.geoJSON(rt.geometry,{weight:6,opacity:.85}).addTo(map); map.fitBounds(routeLine.getBounds(),{padding:[50,50]}); return {km:rt.distance/1000,geometry:rt.geometry};
 }catch{return null}
}
function price(km,type){let base=type==='moto'?5000:7000, per=type==='moto'?1800:2400;return Math.max(base,Math.round(base+km*per))}
function toast(msg,good=false){let x=document.createElement('div');x.className='toast '+(good?'good':'');x.textContent=msg;document.body.appendChild(x);setTimeout(()=>x.remove(),3500)}
function post(data){return fetch('api.php',{method:'POST',body:new URLSearchParams(data)}).then(async r=>{const t=await r.text();try{return JSON.parse(t)}catch{return {ok:false,error:'El servidor no respondió correctamente.'}}}).catch(()=>({ok:false,error:'No se pudo conectar con el servidor.'}))}
function overlay(html,where){let e=$(where);e.innerHTML=html;e.classList.remove('hidden')}
function showBookingContent(){ $('#bookingContent')?.classList.remove('hidden'); }
function hideBookingContent(){ $('#bookingContent')?.classList.add('hidden'); }
function hide(where){$(where)?.classList.add('hidden')}

function setupPassenger(){
  // Los botones se controlan por JS y quedan marcados visualmente con .active.
  document.querySelectorAll('.choice,.pay').forEach(b=>b.type='button');
  // El formulario de tarjeta permanece oculto hasta seleccionar TARJETA.
  selectedPay='efectivo';
  const cardForm=$('#cardForm');
  if(cardForm) cardForm.classList.add('hidden');

  $$('.choice').forEach(b=>b.onclick=(e)=>{
    e.preventDefault();
    $$('.choice').forEach(x=>x.classList.remove('active'));
    b.classList.add('active');
    selectedType=b.dataset.type;
    const d=$('#destination')?.dataset;
    if(d?.lat && d?.lng){
      drawRoute(currentPos,{lat:+d.lat,lng:+d.lng}).then(rt=>{
        if(rt){$('#price').textContent=money(price(rt.km,selectedType));}
      });
    }
  });

  $$('.pay').forEach(b=>b.onclick=(e)=>{
    e.preventDefault();
    $$('.pay').forEach(x=>x.classList.remove('active'));
    b.classList.add('active');
    selectedPay=b.dataset.pay;
    if(cardForm) cardForm.classList.toggle('hidden', selectedPay!=='tarjeta');
    toast(`Pago seleccionado: ${b.textContent.trim()}`, true);
  });

  $('#locBtn').onclick=async()=>{
    if(navigator.geolocation){
      navigator.geolocation.getCurrentPosition(async p=>{
        currentPos={lat:p.coords.latitude,lng:p.coords.longitude};
        userMarker?.setLatLng([currentPos.lat,currentPos.lng]);
        map?.setView([currentPos.lat,currentPos.lng],15);
        $('#origin').value=await reverse(currentPos);
      }, async()=>{$('#origin').value=await reverse(currentPos);});
    } else $('#origin').value=await reverse(currentPos);
  };

  const destination=$('#destination');
  const box=$('#suggestions');
  let timer;
  destination.addEventListener('input',()=>{
    clearTimeout(timer);
    destination.dataset.lat=''; destination.dataset.lng='';
    $('#distance').textContent='Selecciona un destino';
    $('#price').textContent='$0';
    const q=destination.value.trim();
    timer=setTimeout(()=>showDestinationSuggestions(q),220);
  });

  document.addEventListener('click',e=>{
    if(!e.target.closest('.search-wrap')) box.innerHTML='';
  });

  $('#requestBtn').onclick=requestRide;
  checkPassengerStatus();
  poller=setInterval(checkPassengerStatus,2500);
}

function destinationSearchTerms(q){
 const n=norm(q);
 const terms=[q];
 // Ayuda a que búsquedas cortas como "clini" o "uni" encuentren lugares reales
 // sin obligar al usuario a escoger una categoría.
 if(/^clini/.test(n)) terms.push('clínica', 'clinica');
 if(/^hospi/.test(n)) terms.push('hospital');
 if(/^uni/.test(n)) terms.push('universidad');
 if(/^coleg/.test(n)) terms.push('colegio');
 if(/^centro\s*com/.test(n) || /^cacique/.test(n)) terms.push('centro comercial');
 if(/^mall/.test(n)) terms.push('centro comercial');
 if(/^gim/.test(n)) terms.push('gimnasio');
 if(/^rest/.test(n)) terms.push('restaurante');
 return [...new Set(terms)];
}
function escapeHtml(v){return String(v).replace(/[&<>'"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c]));}
function norm(v){return String(v).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
async function showDestinationSuggestions(q){
 const box=$('#suggestions');
 if(!q){box.innerHTML='';return;}
 box.innerHTML='<div class="suggestion-empty">Buscando lugares en Santander…</div>';
 if(q.length<2){box.innerHTML='';return;}
 const terms=destinationSearchTerms(q);
 const all=[];
 for(const term of terms){
   const places=await geocode(term);
   all.push(...places);
   if(all.length>=12) break;
 }
 const seen=new Set();
 const places=all.filter(x=>{
   const key=`${x.lat},${x.lon}`;
   if(seen.has(key)) return false;
   seen.add(key); return true;
 }).slice(0,10);
 if(!places.length){
   box.innerHTML='<div class="suggestion-empty">No encontré lugares que coincidan en Santander. Prueba con otra palabra, dirección o nombre de establecimiento.</div>';
   return;
 }
 box.innerHTML=places.map(x=>`<button type="button" class="suggestion place-suggestion" data-lat="${x.lat}" data-lng="${x.lon}" data-name="${escapeHtml(x.display_name)}"><span class="suggestion-icon">📍</span><span><b>${escapeHtml(x.display_name.split(',')[0])}</b><small>${escapeHtml(x.display_name)}</small></span></button>`).join('');
 $$('.place-suggestion').forEach(b=>b.onclick=()=>selectDestination(+b.dataset.lat,+b.dataset.lng,b.dataset.name));
}
async function searchTextDestination(q){ showDestinationSuggestions(q); }
async function selectDestination(lat,lng,name){
 const box=$('#suggestions'); $('#destination').value=name; box.innerHTML='';
 $('#destination').dataset.lat=lat; $('#destination').dataset.lng=lng;
 const rt=await drawRoute(currentPos,{lat,lng});
 if(rt){$('#distance').textContent=`${rt.km.toFixed(1)} km aprox.`;$('#price').textContent=money(price(rt.km,selectedType));}
}

async function requestRide(){
 let d=$('#destination').dataset;if(!d.lat){toast('Selecciona un destino de Santander.');return}
 let rt=await drawRoute(currentPos,{lat:+d.lat,lng:+d.lng});if(!rt)return;
 let precio=price(rt.km,selectedType);
 if(selectedPay==='tarjeta'){let nums=$('#cardForm input')?.value||''; if(!nums){toast('Completa los datos de tarjeta.');return}}
 let res=await post({action:'request',tipo:selectedType,origen:$('#origin').value||'Mi ubicación',destino:$('#destination').value,olat:currentPos.lat,olng:currentPos.lng,dlat:d.lat,dlng:d.lng,pago:selectedPay,precio});
 if(res.ok){toast('Solicitud enviada. Buscando conductor…',true);$('#requestBtn').disabled=true;checkPassengerStatus()}
 else toast(res.error||'No se pudo solicitar');
}
async function checkPassengerStatus(){
 let r=await post({action:'status'});if(!r.ok)return;
 if(r.viaje){
   if(r.viaje.estado==='cancelado'){
     const key=`rideshare_cancel_survey_${r.viaje.id}`;
     if(r.viaje.motivo_cancelacion==='Cancelado por conductor' && !localStorage.getItem(key)){
       activeRide=r.viaje; renderPassengerRide(r.viaje); showRate(r.viaje.id,true); localStorage.setItem(key,'1'); return;
     }
     activeRide=null; $('#requestBtn').disabled=false; hide('#rideOverlay'); showBookingContent();
     return;
   }
   if(r.viaje.estado==='finalizado'){
     const key=`rideshare_rate_survey_${r.viaje.id}`;
     if(!localStorage.getItem(key)){
       activeRide=r.viaje; renderPassengerRide(r.viaje); showRate(r.viaje.id,false); localStorage.setItem(key,'1'); return;
     }
     activeRide=null; $('#requestBtn').disabled=false; hide('#rideOverlay'); showBookingContent();
     return;
   }
   // Actualiza también cuando cambia el estado del mismo viaje
   // (solicitado -> aceptado -> en_curso), sin necesidad de F5.
   const changed=!activeRide || activeRide.id!==r.viaje.id || activeRide.estado!==r.viaje.estado;
   activeRide=r.viaje;
   if(changed) renderPassengerRide(r.viaje); else updateChatNotification(r.viaje);
 }
 if(!r.viaje && activeRide){
   activeRide=null;
   $('#requestBtn').disabled=false;
   hide('#rideOverlay');
   showBookingContent();
   if(routeLine){map.removeLayer(routeLine);routeLine=null}
 }
}
function passengerRoutePoints(v){
 const olat=Number(v.origen_lat), olng=Number(v.origen_lng), dlat=Number(v.destino_lat), dlng=Number(v.destino_lng);
 if(Number.isFinite(olat)&&Number.isFinite(olng)&&Number.isFinite(dlat)&&Number.isFinite(dlng)&&olat&&olng&&dlat&&dlng){
   return {origin:{lat:olat,lng:olng},destination:{lat:dlat,lng:dlng}};
 }
 return null;
}
async function showPassengerRoute(v){
 const pts=passengerRoutePoints(v);
 if(!pts)return null;
 return drawRoute(pts.origin,pts.destination);
}
function makeVehicleMarker(v,latlng){
 const icon=L.divIcon({className:'car-marker passenger-vehicle-marker',html:v.tipo_vehiculo==='moto'?'🏍️':'🚗',iconSize:[40,40],iconAnchor:[20,20]});
 return L.marker(latlng,{icon,zIndexOffset:1200});
}
function clearDriverMarker(){
 if(driverMarker&&map){map.removeLayer(driverMarker);driverMarker=null;}
}
async function animatePassengerVehicle(v){
 const pts=passengerRoutePoints(v);
 if(!pts)return;
 const rt=await showPassengerRoute(v);
 const coords=rt?.geometry?.coordinates||[];
 if(!coords.length)return;
 clearDriverMarker();
 driverMarker=makeVehicleMarker(v,[coords[0][1],coords[0][0]]).addTo(map);
 let i=0;
 const total=Math.min(coords.length,90);
 const stepSize=Math.max(1,Math.ceil(coords.length/total));
 const path=coords.filter((_,idx)=>idx%stepSize===0);
 if(path[path.length-1]!==coords[coords.length-1])path.push(coords[coords.length-1]);
 const move=()=>{
   if(!driverMarker||!map)return;
   if(i>=path.length-1){driverMarker.setLatLng([path[path.length-1][1],path[path.length-1][0]]);return;}
   i++;
   driverMarker.setLatLng([path[i][1],path[i][0]]);
   setTimeout(move,650);
 };
 move();
}
function showPersonProfile(v,who){
 const isDriver=who==='conductor';
 const name=isDriver?`${v.conductor_nombre||''} ${v.conductor_apellido||''}`.trim():`${v.pasajero_nombre||''} ${v.pasajero_apellido||''}`.trim();
 const phone=isDriver?v.conductor_telefono:v.pasajero_telefono;
 const photo=isDriver?v.conductor_foto:v.pasajero_foto;
 const modal=$('#profileModal'); if(!modal)return;
 $('#profilePhoto').src=photo?`uploads/${photo}`:'assets/avatar.svg';
 $('#profileName').textContent=name|| (isDriver?'Conductor':'Pasajero');
 $('#profileRole').textContent=isDriver?'CONDUCTOR':'PASAJERO';
 $('#profilePhone').textContent=phone||'No registrado';
 $('#profilePlate').textContent=isDriver?(v.placa||'No registrada'):'No aplica';
 $('#profileVehicle').textContent=isDriver?(v.tipo_vehiculo||'No registrado'):'No aplica';
 $('#profileColor').textContent=isDriver?(v.color_vehiculo||'No registrado'):'No aplica';
 modal.classList.remove('hidden');
}

function renderPassengerRide(v){
 if(v.estado==='cancelado'){hideBookingContent();overlay(`<div class="ride-status"><b>⚠️ El conductor ha cancelado el viaje</b><div class="route-mini">📍 ${escapeHtml(v.origen)}<br>🎯 ${escapeHtml(v.destino)}</div></div>`,'#rideOverlay');return;}
 let accepted=v.estado!=='solicitado';
 let html=`<div class="ride-status"><b>${v.estado==='solicitado'?'🔎 Buscando conductor…':v.estado==='aceptado'?'🚗 Conductor asignado':'🛣️ Viaje en curso'}</b>
 ${accepted?`<div class="driver-box clickable-profile" id="passengerDriverProfile" title="Haz clic en la foto o en la tarjeta para ver la información del conductor"><button type="button" class="profile-photo-btn" id="passengerDriverPhoto" aria-label="Ver información del conductor"><img src="${v.conductor_foto?'uploads/'+v.conductor_foto:'assets/avatar.svg'}" alt="Foto del conductor"></button><div><strong>${escapeHtml(v.conductor_nombre||'Conductor')}</strong><small>${escapeHtml(v.placa||'')} · ${escapeHtml(v.color_vehiculo||'')} · ${escapeHtml(v.tipo_vehiculo||'')}</small><em class="profile-hint">Ver información del conductor</em></div><span class="profile-chevron">›</span></div> <div class="actions"><a class="action" href="tel:${v.conductor_telefono||''}">📞 Llamar</a><button class="action" id="chatBtn">💬 Chatear</button><button class="action emergency" id="emergency">🆘 Auxilio</button></div>`:''}
 <div class="route-mini">📍 ${v.origen}<br>🎯 ${v.destino}<br><b>${money(v.precio)}</b> · ${v.medio_pago}</div>
 ${accepted&&v.estado==='aceptado'?'<button class="danger-outline" id="cancelBtn">Cancelar solicitud</button>':''}
 </div>`;
 hideBookingContent();
 overlay(html,'#rideOverlay');
 $('#passengerDriverProfile')?.addEventListener('click',(e)=>{if(e.target.closest('.profile-photo-btn')||e.currentTarget===e.target||e.target.closest('.driver-box'))showPersonProfile(v,'conductor')});
 $('#chatBtn')?.addEventListener('click',()=>openChat(v));
 updateChatNotification(v);
 $('#cancelBtn')?.addEventListener('click',()=>$('#cancelModal').classList.remove('hidden'));
 $('#emergency')?.addEventListener('click',()=>toast('Emergencia activada. Busca también ayuda local inmediata.',true));
 if(v.estado==='en_curso')animatePassengerVehicle(v);
 if(v.estado==='finalizado')showRate(v.id,false);
}
function showRate(id,cancelled=false){
  $('#rateModal h3').textContent=cancelled?'El conductor ha cancelado el viaje':'¿Cómo fue tu viaje?';
  $('#rateModal p')?.remove();
  $('#rateModal').classList.remove('hidden');
  $('#stars').dataset.stars='';
  $('#stars').innerHTML=[1,2,3,4,5].map(n=>`<button type="button" class="star-btn" data-star="${n}" aria-label="${n} estrella${n>1?'s':''}">★</button>`).join('');
  $$('#stars .star-btn').forEach(btn=>btn.onclick=()=>{
    const n=Number(btn.dataset.star); $('#stars').dataset.stars=String(n);
    $$('#stars .star-btn').forEach(x=>x.classList.toggle('selected',Number(x.dataset.star)<=n));
  });
  $('#rateBtn').onclick=async()=>{
    let stars=Number($('#stars').dataset.stars||5);
    let r=await post({action:'rate',viaje_id:id,estrellas:stars,comentario:$('#comment').value});
    if(r.ok){
      $('#rateModal').classList.add('hidden');
      activeRide=null;
      localStorage.setItem(`rideshare_rate_survey_${id}`,'1');
      localStorage.setItem(`rideshare_cancel_survey_${id}`,'1');
      location.reload();
    }
  };
  $('#skipRate').onclick=()=>{
    $('#rateModal').classList.add('hidden');
    activeRide=null;
    localStorage.setItem(`rideshare_rate_survey_${id}`,'1');
    localStorage.setItem(`rideshare_cancel_survey_${id}`,'1');
    location.reload();
  };
}

function chatReadKey(id){return `rideshare_chat_read_${id}_${C.userId}`}
function markChatRead(id,lastId){if(lastId)localStorage.setItem(chatReadKey(id),String(lastId));setChatBadge(false,0)}
function setChatBadge(show,count=0){const b=$('#chatBtn');if(!b)return;b.classList.toggle('chat-unread',show);let badge=b.querySelector('.chat-badge');if(show){if(!badge){badge=document.createElement('span');badge.className='chat-badge';b.appendChild(badge)}badge.textContent=count>1?count:'1';}else if(badge)badge.remove()}
async function updateChatNotification(v){
 if(!v?.id||v.estado==='finalizado'||v.estado==='cancelado')return;
 const r=await post({action:'chat',viaje_id:v.id}); if(!r.ok)return;
 const msgs=r.mensajes||[]; if(!msgs.length)return;
 const lastId=+msgs[msgs.length-1].id||0;
 const incoming=msgs.filter(m=>+m.emisor_id!==+C.userId);
 if(!incoming.length)return;
 const key=chatReadKey(v.id); const saved=localStorage.getItem(key);
 if(!saved){localStorage.setItem(key,String(lastId));return;}
 const unread=incoming.filter(m=>(+m.id||0)>+saved);
 setChatBadge(unread.length>0,unread.length);
}
function openChat(v){
 $('#chatModal').classList.remove('hidden');
 loadChat(v.id,true);
 $('#chatForm').onsubmit=async e=>{e.preventDefault();const input=$('#chatInput');const msg=input.value.trim();if(!msg)return;const r=await post({action:'send_chat',viaje_id:v.id,mensaje:msg});if(r.ok){input.value='';await loadChat(v.id,true)}};
}
async function loadChat(id,markRead=false){let r=await post({action:'chat',viaje_id:id});if(!r.ok)return;const msgs=r.mensajes||[];$('#chatMessages').innerHTML=msgs.map(m=>`<div class="msg ${+m.emisor_id===+C.userId?'mine':''}">${escapeHtml(m.mensaje)}<small>${escapeHtml(m.emisor)}</small></div>`).join('');let x=$('#chatMessages');x.scrollTop=x.scrollHeight;if(markRead&&msgs.length)markChatRead(id,+msgs[msgs.length-1].id)}
let lastDriverRequestIds=new Set();
function setupDriver(){checkDriver();poller=setInterval(checkDriver,1800)}
async function checkDriver(){
 let r=await post({action:'status'});
 if(r.viaje){
   const changed=!activeRide || activeRide.id!==r.viaje.id || activeRide.estado!==r.viaje.estado;
   activeRide=r.viaje;
   if(changed) renderDriverRide(r.viaje); else updateChatNotification(r.viaje);
   $('#requestList').innerHTML='';
   $('#driverNotice').innerHTML='';
   return;
 }
 let q=await post({action:'driver_requests'});
 if(!q.ok){$('#driverNotice').innerHTML=`<div class="alert error">${q.error}</div>`;$('#requestList').innerHTML='';return}
 $('#driverNotice').innerHTML='';
 const solicitudes=q.solicitudes||[];
 const newIds=solicitudes.map(v=>String(v.id));
 const hasNew=solicitudes.some(v=>!lastDriverRequestIds.has(String(v.id)));
 if(hasNew){
   const newest=solicitudes.find(v=>!lastDriverRequestIds.has(String(v.id)));
   showDriverRequest(newest);
 }
 lastDriverRequestIds=new Set(newIds);
 $('#requestList').innerHTML=solicitudes.length?solicitudes.map(v=>`<div class="request request-card">
   <div class="request-main"><div class="request-head"><span class="request-icon">🔔</span><span class="tag">NUEVA SOLICITUD</span></div>
   <h3>${escapeHtml(v.origen)} <span>→</span> ${escapeHtml(v.destino)}</h3>
   <div class="request-meta"><span>👤 ${escapeHtml(v.pasajero_nombre||'Pasajero')}</span><span>🚘 ${escapeHtml(v.tipo_vehiculo||'')}</span><span>💳 ${escapeHtml(v.medio_pago||'')}</span></div>
   <div class="request-price">${money(v.precio)}</div></div>
   <div class="req-actions"><button class="secondary reject" data-id="${v.id}">Rechazar</button><button class="primary accept" data-id="${v.id}">Aceptar viaje</button></div>
 </div>`).join(''):'<div class="empty">No hay solicitudes nuevas. Te avisaremos automáticamente.</div>';
 $$('.accept').forEach(b=>b.onclick=async()=>{b.disabled=true;b.textContent='Aceptando…';let x=await post({action:'accept',viaje_id:b.dataset.id});toast(x.ok?'¡Viaje aceptado! El pasajero será notificado.':'No fue posible aceptarla',x.ok);lastDriverRequestIds.delete(String(b.dataset.id));checkDriver()});
 $$('.reject').forEach(b=>b.onclick=async()=>{b.disabled=true;await post({action:'delete_request',viaje_id:b.dataset.id});checkDriver()});
}
function showDriverRequest(v){
 if(!v)return;
 const existing=$('#incomingRequest');
 if(existing) existing.remove();
 const el=document.createElement('div');
 el.id='incomingRequest';
 el.className='incoming-request';
 el.innerHTML=`<div class="incoming-backdrop"></div><div class="incoming-box">
   <button class="incoming-close" aria-label="Cerrar">×</button>
   <div class="incoming-icon">🔔</div><span class="incoming-label">NUEVA SOLICITUD</span>
   <h2>¡Tienes un nuevo viaje!</h2>
   <p class="incoming-route">📍 ${escapeHtml(v.origen)} <span>→</span> 🎯 ${escapeHtml(v.destino)}</p>
   <div class="incoming-info"><div><small>Pasajero</small><b>👤 ${escapeHtml(v.pasajero_nombre||'')}</b></div><div><small>Pago</small><b>${escapeHtml(v.medio_pago||'')}</b></div><div><small>Valor</small><b>${money(v.precio)}</b></div></div>
   <div class="incoming-actions"><button class="secondary incoming-reject">Rechazar</button><button class="primary incoming-accept">Aceptar viaje</button></div>
 </div>`;
 document.body.appendChild(el);
 const close=()=>el.remove();
 el.querySelector('.incoming-close').onclick=close;
 el.querySelector('.incoming-backdrop').onclick=close;
 el.querySelector('.incoming-reject').onclick=async()=>{await post({action:'delete_request',viaje_id:v.id});lastDriverRequestIds.delete(String(v.id));close();checkDriver()};
 el.querySelector('.incoming-accept').onclick=async()=>{const b=el.querySelector('.incoming-accept');b.disabled=true;b.textContent='Aceptando…';const x=await post({action:'accept',viaje_id:v.id});if(x.ok){toast('¡Viaje aceptado! El pasajero lo verá automáticamente.',true);close();lastDriverRequestIds.delete(String(v.id));checkDriver()}else{toast(x.error||'No fue posible aceptarla');b.disabled=false;b.textContent='Aceptar viaje'}};
 setTimeout(()=>el.classList.add('show'),20);
}
async function renderDriverRide(v){
 let html=`<div class="ride-status"><b>${v.estado==='aceptado'?'🚗 Viaje aceptado':'🛣️ Recorrido en curso'}</b>
 <div class="driver-box clickable-profile" id="driverPassengerProfile" title="Haz clic en la foto o en la tarjeta para ver la información del pasajero"><button type="button" class="profile-photo-btn" id="driverPassengerPhoto" aria-label="Ver información del pasajero"><img src="${v.pasajero_foto?'uploads/'+v.pasajero_foto:'assets/avatar.svg'}" alt="Foto del pasajero"></button><div><strong>${escapeHtml(v.pasajero_nombre||'Pasajero')}</strong><small>Tel: ${escapeHtml(v.pasajero_telefono||'—')}</small><em class="profile-hint">Ver información del pasajero</em></div><span class="profile-chevron">›</span></div>
 <div class="route-mini">📍 Recogida: ${v.origen}<br>🎯 Destino: ${v.destino}<br>💳 ${v.medio_pago} · <b>${money(v.precio)}</b></div>
 <div class="actions"><a class="action" href="tel:${v.pasajero_telefono||''}">📞 Llamar</a><button class="action" id="chatBtn">💬 Chatear</button><button class="action emergency" id="emergency">🆘 Auxilio</button></div>
 ${v.estado==='aceptado'?'<button class="primary" id="startBtn">▶ Iniciar recorrido</button>':'<button class="primary" id="finishBtn" disabled>✓ Finalizar recorrido</button>'}<button class="danger-outline" id="driverCancelBtn">Cancelar viaje</button></div>`;
 overlay(html,'#driverRide');$('#driverPassengerProfile')?.addEventListener('click',()=>showPersonProfile(v,'pasajero'));$('#chatBtn').onclick=()=>openChat(v);updateChatNotification(v);$('#emergency').onclick=()=>toast('Emergencia activada. Busca también ayuda local inmediata.',true);
 let rt=await drawRoute({lat:+v.origen_lat,lng:+v.origen_lng},{lat:+v.destino_lat,lng:+v.destino_lng});
 if(v.estado==='aceptado')$('#startBtn').onclick=async()=>{let r=await post({action:'start',viaje_id:v.id});if(r.ok){animateVehicle(rt?.geometry?.coordinates||[],v);checkDriver()}};
 if(v.estado==='en_curso')animateVehicle(rt?.geometry?.coordinates||[],v);
 $('#driverCancelBtn')?.addEventListener('click',async()=>{if(!confirm('¿Seguro que deseas cancelar este viaje?'))return;const r=await post({action:'cancel_driver',viaje_id:v.id});if(r.ok){toast('Viaje cancelado. El pasajero será notificado.',true);setTimeout(()=>location.reload(),700)}else toast(r.error||'No se pudo cancelar el viaje.')});
 $('#finishBtn')?.addEventListener('click',async()=>{let r=await post({action:'finish',viaje_id:v.id});if(r.ok){toast('Recorrido finalizado.',true);setTimeout(()=>location.reload(),700)}});
}
function animateVehicle(coords,v){if(!coords.length)return; if(coords.length>70){let step=Math.ceil(coords.length/70);coords=coords.filter((_,i)=>i%step===0);} if(driverMarker)map.removeLayer(driverMarker);driverMarker=L.marker([coords[0][1],coords[0][0]],{icon:L.divIcon({className:'car-marker',html:v.tipo_vehiculo==='moto'?'🏍️':'🚗',iconSize:[36,36]})}).addTo(map);
 let i=0;function step(){if(i>=coords.length-1){ if($('#finishBtn')){$('#finishBtn').disabled=false;$('#finishBtn').title='El vehículo llegó al destino';} return;} i++;driverMarker.setLatLng([coords[i][1],coords[i][0]]);setTimeout(step,650)}step()}
function setupMenu(){
 $('#menuBtn').onclick=()=>$('#menu').classList.toggle('open');
 document.addEventListener('click',e=>{if(!e.target.closest('.user-menu'))$('#menu')?.classList.remove('open')});
 $$('[data-panel="history"]').forEach(x=>x.onclick=async e=>{e.preventDefault();$('#menu')?.classList.remove('open');$('#historyPanel')?.classList.remove('hidden');let box=$('#historyContent');if(!box)return;box.innerHTML='<div class="history-loading">Cargando historial…</div>';let r=await post({action:'history'});box.innerHTML=(r.historial||[]).map(v=>`<div class="history-item"><div class="history-icon">${v.estado==='finalizado'?'✓':v.estado==='cancelado'?'×':'🚗'}</div><div><b>${escapeHtml(v.origen)} → ${escapeHtml(v.destino)}</b><small>${escapeHtml(v.creado_en)} · ${escapeHtml(v.estado)} · ${money(v.precio)}</small></div></div>`).join('')||'<div class="history-empty">No tienes viajes registrados.</div>'});
 $$('[data-panel="surveys"]').forEach(x=>x.onclick=async e=>{e.preventDefault();$('#menu')?.classList.remove('open');$('#surveysPanel')?.classList.remove('hidden');let box=$('#surveysContent');if(!box)return;box.innerHTML='<div class="history-loading">Cargando encuestas…</div>';let r=await post({action:'surveys'});box.innerHTML=(r.encuestas||[]).map(v=>`<div class="history-item survey-item"><div class="history-icon">⭐</div><div><b>${escapeHtml(v.pasajero_nombre||'Pasajero')}</b><div class="survey-stars">${'★'.repeat(Number(v.estrellas)||0)}${'☆'.repeat(5-(Number(v.estrellas)||0))}</div><small>${escapeHtml(v.comentario||'Sin comentario')} · ${escapeHtml(v.creado_en)}</small></div></div>`).join('')||'<div class="history-empty">Aún no tienes encuestas.</div>'});
}
$('#cancelForm')?.addEventListener('submit',async e=>{
 e.preventDefault();
 if(!activeRide){toast('No hay una solicitud activa para cancelar.');return}
 const btn=e.submitter||$('#cancelForm button[type=submit]');
 if(btn){btn.disabled=true;btn.textContent='Cancelando…'}
 try{
   let r=await post({action:'cancel',viaje_id:activeRide.id,motivo:$('#cancelReason').value});
   if(r.ok){
     $('#cancelModal').classList.add('hidden');
     activeRide=null;
     $('#requestBtn').disabled=false;
     hide('#rideOverlay');
     showBookingContent();
     if(routeLine){map.removeLayer(routeLine);routeLine=null}
     toast('Viaje cancelado correctamente.',true);
     setTimeout(()=>location.reload(),700);
   }else{
     toast(r.error||'No se pudo cancelar el viaje.');
   }
 }catch(err){toast('No se pudo conectar con el servidor.')}
 finally{if(btn){btn.disabled=false;btn.textContent='Confirmar cancelación'}}
});
$$('[data-close]').forEach(x=>x.onclick=()=>x.closest('.modal').classList.add('hidden'));
$$('[data-side-close]').forEach(x=>x.onclick=()=>x.closest('.side-modal').classList.add('hidden'));
$$('.side-modal').forEach(x=>x.addEventListener('click',e=>{if(e.target===x)x.classList.add('hidden')}));
document.addEventListener('DOMContentLoaded',()=>{initMap();setupMenu();C.role==='pasajero'?setupPassenger():setupDriver()});
