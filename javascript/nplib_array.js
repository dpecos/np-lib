/*Array.prototype.sort = function()
{
   var tmp;
   for(var i=0;i<this.length;i++)
   {
       for(var j=0;j<this.length;j++)
       {
           if(this[i]<this[j])
           {
               tmp = this[i];
               this[i] = this[j];
               this[j] = tmp;
           }
       }
   }
};*/

Array.prototype.unshift = function(item)
{
   this[this.length] = null;
   for(var i=1;i<this.length;i++)
   {
       this[i] = this[i-1];
   }
   this[0] = item;
};

Array.prototype.shift = function()
{
   for(var i=1;i<this.length;i++) {
       this[i-1] = this[i];
   }
   this.length =  this.length-1;
};


Array.prototype.clear = function()
{
   this.length = 0;
};

Array.prototype.contains = function (element)
{
	for (var i = 0; i < this.length; i++) {
		if (this[i] == element)
			return true;
	}
	return false;
};

Array.prototype.shuffle = function()
{
   var i=this.length,j,t;
   while(i--) {
      j=Math.floor((i+1)*Math.random());
      t=arr[i];
      arr[i]=arr[j];
      arr[j]=t;
   }
};

Array.prototype.unique = function()
{
   var a=[],i;
   this.sort();
   for(i=0; i<this.length; i++) {
      if(!a.contains(this[i]))
         a[a.length] = this[i];
   }
   return a;
};

Array.prototype.lastIndexOf = function(n)
{
   var i=this.length;
   while(i--) {
      if(this[i]===n)
         return i;
   }
   return -1;
};