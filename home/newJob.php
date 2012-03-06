<section class="hidden" id="njBack">

	<!-- Frame 1 -->
	<div class="njFrame" id="njFrame1">
	
		<div class="njbText1">Enter new job details</div>
		<div class="njbText2">Please enter the details for the policy holder.</div>
	
		<a class="njField njFieldTop">
		
			<label>First name</label>
			<input id="njFirstName" name="njFirstName" type="text" tag=""/>
			
		</a>
		<a class="njField">
		
			<label>Last name</label>
			<input id="njLastName" name="njLastName" type="text" tag=""/>
			
		</a>
		
		<a class="njField njFieldTop">
		
			<label>Address</label>
			<input id="njAddress" name="njAddress" type="text" tag=""/>
			
		</a>
		<a class="njField">
		
			<label>Suburb</label>
			<input id="njSuburb" name="njSuburb" type="text" tag=""/>
			
		</a>
		<a class="njField">
		
			<label>Postcode</label>
			<input id="njPostCode" name="njPostCode" type="text" tag=""/>
			
		</a>
		
		<a class="njField njFieldTop">
		
			<label>Mobile</label>
			<input id="njMobile" name="njMobile" type="text" tag=""/>
			
		</a>
		<a class="njField">
			
			<label>Landline</label>
			<input id="njLandline" name="njLandline" type="text" tag=""/>
			
		</a>
		
		<a class="njField njFieldTop" id="njInsurerPicker">
			
			<label>Insurer</label>
			<select class="njSelect" id="njInsurer" name="njInsurer" type="text" tag="">
			</select>
			
		</a>
		<a class="njField">
			
			<label>Claim No.</label>
			<input id="njClaimNumber" name="njClaimNumber" type="text" tag=""/>
			
		</a>
		
		<a class="navButton navLeft"  id="njNavCancel">Cancel</a>
		<a class="navButton navRight" id="njNavNext"  >Next</a>
		
	</div>
	
	<!-- Frame 2 --->
	<div class="njFrame" id="njFrame2">
		
		<p class="njLabel">Notes</p>
		<textarea id="njBrief" placeholder="Enter instructions for the inspector"></textarea>
		
		<p class="njLabel">Choose required reports</p>
		<div class="njReports">
		
			<p class="njCheck">
			
				<input id="njCausation" type="checkbox" name="report" value="causation"/>
				<label>Causation Report</label>
				
			</p>
			<p class="njCheck">
			
				<input id="njScope" type="checkbox" name="report" value="scope"/>
				<label>Scope of Works</label>
				
			</p>
			<p class="njCheck">
			
				<input id="njCosting" class="future" type="checkbox" name="report" value="costing"/>
				<label class="future">Costing Report</label>
				
			</p>
			
		</div>
		
		<a class="njField" id="njOwnerPicker">
		
			<label>Job Owner</label>
			<select class="njSelect" id="njOwner" name="njOwner" type="text" tag="">
			</select>
			
		</a>
			
		<a class="njField" id="njInspectorPicker">
		
			<label>Inspector</label>
			<select class="njSelect" id="njInspector" name="njInspector" type="text" tag="">				
			</select>
			
		</a>
		
		<a class="navButton navLeft" id="njNavBack" >Back</a>
		<a class="navButton navRight" id="njNavSave">Save<a/>
		
	</div>
</section>