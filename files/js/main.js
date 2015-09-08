window.onload = function() {
  var menuBtn = document.getElementById('expand');
  
  menuBtn.addEventListener('click', function() {
    var menu = document.getElementById('menu').children[1];
    
    if (!menu.className) {
      menu.className = 'slidedown';
    } else {
      menu.className = '';
    }
  });
  
  var pre = document.getElementsByTagName('pre');
  
  for (var i = 0; i < pre.length; ++i) {
    if (pre[i].children[0].tagName.toLowerCase() == 'code') {
      hljs.highlightBlock(pre[i].children[0]);
    }
  }
  /*
  var content = document.getElementById('content');
  
  if (content) {
    var CD  = document.createElement('div');
    var CDb = document.createElement('button');
    
    CD.id = 'comments';
    CDb.innerHTML = 'Add a comment';
    
    CDb.addEventListener('click', function() {
      var CD = this.parentNode;
      this.style.display = 'none';
      
      var form = document.createElement('form');
      form.name = 'cmtForm';
      form.method = 'post';
      form.action = 'javascript:void(0);';
      
      var formDiv = document.createElement('div');
      form.innerHTML = 'Nickname:<input type="text" name="name" /> Comment:<textarea name="comment"></textarea>';
      
      var formBtn   = document.createElement('input');
      formBtn.type  = 'submit';
      formBtn.name  = 'submit';
      formBtn.value = 'Submit';
      
      formBtn.addEventListener('click', function(e) {
        e.preventDefault();
        this.disabled = 'disabled';
        
        var data = {};
        var formElements = form.getElementsByTagName('input');
        
        for (var formIdx in formElements) {
          var formElement = formElements[formIdx];

          if (formElement.name && formElement.value) {
            data[formElement.name] = formElement.value.trim().replace(/(<([^>]+)>)/ig,'');
          }
        }
        
        formElements = form.getElementsByTagName('textarea');
        
        for (formIdx in formElements) {
          var formElement = formElements[formIdx];
          
          if (formElement.name && formElement.value) {
            data[formElement.name] = formElement.value.trim().replace(/(<([^>]+)>)/ig,'');
          }
        }
        
        validateInput(this, data);
      });
      
      formDiv.appendChild(formBtn);
      form.appendChild(formDiv);
      CD.appendChild(form);
    });
    
    CD.appendChild(CDb);
    content.appendChild(CD);
  }*/
}
/*
function validateInput(btn, data) {
  if (!btn || !data) {
    return;
  }
  
  var currentUrl = window.location.href;
  
  if (!currentUrl) {
    printError(btn, 'Failed to retrieve URL of current article.');
    return;
  }
  
  if (!data['name'] || data['name'].length < 3) {
    printError(btn, 'You have to specify a valid nickname.');
    return;
  }
  
  if (!data['name'].length > 128) {
    printError(btn, 'Too long nickname, please limit it to 32 chars. Your name is '
      + data['name'].length + ' characters long.');
    return;
  }
  
  if (!data['comment'] || data['comment'].length < 5) {
    printError(btn, 'Please provide a valid comment, plain text only.');
    return;
  }
  
  if (!data['comment'].length > 512) {
    printError(btn, 'Too long comment, please limit it to 500 chars. Your comment is '
      + data['comment'].length + ' characters long.');
    return;
  }
  
  var baseNameStart = currentUrl.lastIndexOf('/');
  var baseNameEnd = currentUrl.lastIndexOf('.');
  
  if ((baseNameStart == -1) || (baseNameEnd == -1)) {
    printError(btn, 'Failed to retrieve basename of current article.');
    return;
  }
  
  baseNameStart += 1;
  currentUrl = currentUrl.substr(baseNameStart, (baseNameEnd - baseNameStart));
  
  var pageSeparator = currentUrl.lastIndexOf('-');
  if (pageSeparator > 0) {
    var pageSepString = currentUrl.substr(pageSeparator+1, 2);
    var re = /^p\d$/;
    
    if (pageSepString.match(re)) {
      currentUrl = currentUrl.substr(0, pageSeparator);
    }
  }
  
  data['id'] = currentUrl;
  
  var xhReq = new XMLHttpRequest();
  xhReq.open('POST', '../php/post.php');
  xhReq.setRequestHeader('Content-Type', 'application/json')
  xhReq.send(JSON.stringify(data));
}

function printError(btn, msg) {
  alert(msg);
  btn.removeAttribute('disabled');
}*/