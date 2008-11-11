<div id="mainAdmin">
	<div id="groupContainer1" class="groupWrapper">
		<div id="one" class="groupItem">
			<div class="itemHeader" id="item_{$i}"><span>Newest Users</span><a href="#" class="closeEl">[-]</a></div>
			<div class="itemContent">
				{include file="user.list.tpl"}
			</div>
		</div>
		<div id="two" class="groupItem">
			<div class="itemHeader" id="item_{$i}"><span>Recent Posts</span><a href="#" class="closeEl">[-]</a></div>
			<div class="itemContent">
				
			</div>
		</div>
	</div>
	<div id="groupContainer2" class="groupWrapper">
		<div id="three" class="groupItem">
			<div class="itemHeader" id="item_{$i}"><span>Browser Stats</span><a href="#" class="closeEl">[-]</a></div>
			<div class="itemContent">
				<img src="files/browsergraph.png" />
			</div>
		</div>
		<div id="four" class="groupItem">
			<div class="itemHeader" id="item_{$i}"><span>Recent Events</span><a href="#" class="closeEl">[-]</a></div>
			<div class="itemContent">
				
			</div>
		</div>
	</div>
</div>