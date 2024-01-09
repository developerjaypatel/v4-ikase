<select id="specialty" name="specialty" required>
	<option value="">Select from List</option>
    <% 
    _.each( specialties, function(specialty) {
    %>
    <option value="<%=specialty.specialty %>"><%=specialty.specialty %></option>
    <% }); %>
</select>
