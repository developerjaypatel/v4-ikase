SELECT DISTINCT pers.* 
FROM barsoum.card, barsoum.barsoum_person pers
WHERE card.`LAST` = pers.last_name
AND card.`FIRST` = pers.first_name
AND card.`BIRTH_DATE` = pers.dob
AND card.`LAST` = 'Pineda'
AND card.`FIRST` = 'Luis Romeo'

SELECT * FROM barsoum.card2
WHERE FIRMCODE = '16060'

SELECT * FROM barsoum.barsoum_person
WHERE parent_person_uuid = 'PA5b8e00240cf4a'
last_name = 'Pineda'
AND first_name = 'Luis Romeo'

SELECT * FROM barsoum.barsoum_case_person
WHERE person_uuid = 'DR5b8d7654451d6'

SELECT * FROM barsoum.barsoum_case
WHERE case_uuid = 'KS5b8d76543964f'

SELECT * FROM barsoum.injury
WHERE CASENO = '4311'

SELECT * FROM barsoum.`case`
WHERE CASENO = '4311'

SELECT * FROM barsoum.casecard
WHERE CASENO = '4311'

SELECT inj.* 
FROM barsoum.barsoum_injury inj
INNER JOIN barsoum.barsoum_case_injury cinj
ON inj.injury_uuid = cinj.injury_uuid
WHERE case_uuid = 'KS5b8d76543964f'