"use strict";

/* VIVA: Chat UI enhancement only. The PHP server enforces chat ownership, validates submissions and stores messages; JavaScript polls and renders them safely as text. */
(() => {
  const root=document.getElementById('chat-app');if(!root)return;
  const list=document.getElementById('chat-messages');
  const form=document.getElementById('chat-form');
  const field=document.getElementById('message-input');
  const error=document.getElementById('chat-error');
  const myId=Number(root.dataset.user);
  let loading=false;
  /** Load latest messages periodically; skip polling if the tab is hidden. */
  async function refresh(){
    if(loading||document.hidden)return;loading=true;
    try{
      const response=await fetch(root.dataset.fetch,{credentials:'same-origin',cache:'no-store'});
      if(!response.ok)throw new Error('Unable to load messages');
      const data=await response.json();list.replaceChildren();
      for(const message of (data.messages||[])){
        const bubble=document.createElement('article');
        bubble.className='bubble '+(Number(message.sender_user_id)===myId?'mine':'theirs');
        const body=document.createElement('span');body.textContent=message.message_text;
        const time=document.createElement('small');time.textContent=message.sent_at;
        bubble.append(body,time);list.append(bubble);
      }
      list.scrollTop=list.scrollHeight;
      error.textContent='';
    }catch(e){error.textContent='Could not refresh messages. Check your connection.';}
    finally{loading=false;}
  }
  // Send using FormData (including CSRF token); disable button until complete.
  form.addEventListener('submit',async event=>{
    event.preventDefault();if(!field.value.trim())return;
    const button=form.querySelector('button');button.disabled=true;
    try{
      const response=await fetch(root.dataset.send,{method:'POST',body:new FormData(form),credentials:'same-origin'});
      const data=await response.json();if(!response.ok||!data.ok)throw new Error(data.error||'Send failed');
      field.value='';await refresh();
    }catch(e){error.textContent=e.message;}
    finally{button.disabled=false;field.focus();}
  });
  list.scrollTop=list.scrollHeight;
  const timer=setInterval(refresh,8000);
  window.addEventListener('pagehide',()=>clearInterval(timer),{once:true});
})();
