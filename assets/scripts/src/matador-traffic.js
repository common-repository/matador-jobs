class MatadorTraffic{constructor(i,e="{}"){this.domain=i,this.options=JSON.parse(e),this.referrer=document.referrer,this.defaults={host:this.domain,cookieName:"matador_visitor",separator:".",expires:1728e5,labels:{none:"direct(none)",social:"social",referral:"referral",organic:"organic"},queryParams:{campaign:"utm_campaign",source:"utm_source",medium:"utm_medium",term:"utm_term",content:"utm_content"},socialNetworks:{facebook:["/(.+?).facebook./","/(.+?).fb.me/"],linkedin:["/(.+?).linkedin./"],twitter:["/(.+?).twitter./","/(.+?).t.co/"],reddit:["/(.+?).reddit./"],instagram:["/(.+?).instagram./"],youtube:["/(.+?).youtube./"]},searchEngines:{google:["/(.+?).google./"],bing:["/(.+?).bing./"],yahoo:["/(.+?).yahoo./"],aol:["/(.+?).aol./"],baidu:["/(.+?).baidu./"],duckduckgo:["/(.+?).duckduckgo./"]}},this.options=Object.assign(this.defaults,this.options),this.campaign={timestamp:"",sessions:1,campaigns:0,campaign:"",medium:"",source:"",term:"",content:""},this.initialize()}get query(){let i={};return i.campaign=this.queryParameter(this.options.queryParams.campaign),i.source=this.queryParameter(this.options.queryParams.source),i.medium=this.queryParameter(this.options.queryParams.medium),i.term=this.queryParameter(this.options.queryParams.term),i.content=this.queryParameter(this.options.queryParams.content),i}initialize(){let i;this.hasReferrer()&&(this.hasCookie(this.options.cookieName)?(i=this.getCookie(this.options.cookieName),this.parseMatadorCookie(i)):this.hasCookie("__utmz")&&(i=this.getCookie("__utmz"),this.parseUTMZCookie(i)),this.query.campaign||this.query.source?this.query.campaign&&this.query.campaign===this.campaign.campaign||this.query.source&&this.query.source===this.campaign.campaign?this.campaign.sessions++:(this.campaign.campaign=this.query.campaign?this.query.campaign:this.query.source,this.query.source&&this.query.source!==this.campaign.source?this.campaign.source=this.query.source:this.campaign.source="",this.query.medium&&this.query.medium!==this.campaign.medium?this.campaign.medium=this.query.medium:this.campaign.medium="",this.query.content&&this.query.content!==this.campaign.content?this.campaign.content=this.query.content:this.campaign.content="",this.query.term&&this.query.term!==this.campaign.term?this.campaign.term=this.query.term:this.campaign.term="",this.campaign.sessions=1,this.campaign.campaigns++):this.referrer?this.campaign.source===MatadorTraffic.removeProtocol(this.referrer)?this.campaign.sessions++:(this.campaign.campaign="",this.campaign.source=MatadorTraffic.removeProtocol(this.referrer),this.referrerIsA(this.options.socialNetworks)?(this.campaign.source=this.referrerIsA(this.options.socialNetworks),this.campaign.medium=this.options.labels.social):this.referrerIsA(this.options.searchEngines)?(this.campaign.source=this.referrerIsA(this.options.searchEngines),this.campaign.medium=this.options.labels.organic):this.campaign.medium=this.options.labels.referral,this.campaign.term="",this.campaign.content="",this.campaign.sessions=1,this.campaign.campaigns++):this.campaign.source===this.options.labels.none?this.campaign.sessions++:(this.campaign.source=this.options.labels.none,this.campaign.campaign="",this.campaign.medium="",this.campaign.term="",this.campaign.content="",this.campaign.sessions=1,this.campaign.campaigns++),this.setCookie())}queryParameter(i){let e,t,s;for(e of window.location.search.substring(1).split("&")){if([t,s=""]=e.split("="),t===i)break;s=""}return s}hasCookie(i){return-1!==document.cookie.indexOf(i)}getCookie(i){let e,t,s;for(e of document.cookie.split("; "))if([t,s=""]=e.split(/=(.+)/),t===i)return s;return null}setCookie(){let i,e,t,s=new Date;const a=this.campaign,r=this.options;for(i in a.timestamp=Math.floor(s.getTime()/1e3),s.setTime(s.getTime()+r.expires),t=`${a.timestamp}${r.separator}${a.sessions}${r.separator}${a.campaigns}`,delete a.timestamp,delete a.sessions,delete a.campaigns,e="",a)a[i]&&(e+=e?"|":"",e+=`${i}=${a[i]}`);t+=`${r.separator}${e}`,document.cookie=r.cookieName+"="+t.replace(/ /g,"_")+"; expires="+s.toUTCString()+"; domain="+this.domain+"; path=/; samesite=strict; "}hasReferrer(){return this.referrer.split("/")[2]!==location.hostname}referrerIsA(i={}){let e,t,s,a="";if(0===Object.keys(i).length&&i.constructor===Object)return!1;for(t in i){for(s of i[t])if(e=new RegExp(s.slice(1,-1)),this.referrer.match(e)){a=t;break}if(a)break}return a}static removeProtocol(i){return i.replace(/.*?:\/\//g,"")}parseMatadorCookie(i){let e,t,s;for(t of(e=i.split(".",4),this.campaign.timestamp=e[0],this.campaign.sessions=parseInt(e[1],10),this.campaign.campaigns=parseInt(e[2],10),e[3].split("|")))s=t.split("="),this.campaign[s[0]]=s[1]}parseUTMZCookie(i){let e,t,s,a;for(s of(e={utmccn:"campaign",utmcmd:"medium",utmcsr:"source",utmctr:"term",utmcct:"content"},t=i.split(".",5),this.campaign.timestamp=t[1],this.campaign.sessions=parseInt(t[2],10),this.campaign.campaigns=parseInt(t[3]),t[4].split("|")))a=s.split("="),this.campaign[e[a[0]]]=a[1]}}