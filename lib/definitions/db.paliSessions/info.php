<?php

return array(
	"name"=>"en-us|Sessions",
	"singleName"=>"en-us|Session",
	"table"=>"db/paliSessions",
	"key"=>"id",
	"displayName"=>array("item.name"),
	"nameField"=>"name",
	"orderby"=>"startDate",
	"required"=>"name",
	"fields"=>array(
		"name"=>array(
			"label"=>"en-us|Name",
			"searchable"=>true,
			"cleaners"=>"trim"
		),
		"type"=>array(
			"label"=>"en-us|Type",
			"searchable"=>true,
			"type"=>"select",
			"options"=>array(
				"one-week"=>"en-us|One Week",
				"two-week"=>"en-us|Two Week",
				"four-week"=>"en-us|Four Week"
			)
		),
		"startDate"=>array(
			"label"=>"en-us|Start Date",
			"type"=>"date"
		),
		"endDate"=>array(
			"label"=>"en-us|End Date",
			"type"=>"date"
		)
	)
);
