SELECT activity_user_id, YEAR(activity_date) activity_year, MONTH(activity_date) activity_month, activity_category, COUNT(activity_id) activity_count
FROM ikase_reino.cse_activity 
WHERE activity_user_id > 0
GROUP BY activity_user_id, YEAR(activity_date), MONTH(activity_date), activity_category
ORDER BY activity_user_id ASC, activity_category ASC, YEAR(activity_date), MONTH(activity_date)

SELECT activity_user_id, YEAR(activity_date) activity_year, MONTH(activity_date) activity_month, COUNT(activity_id) activity_count
FROM ikase_reino.cse_activity 
WHERE activity_user_id > 0
GROUP BY activity_user_id, YEAR(activity_date), MONTH(activity_date)
ORDER BY activity_user_id ASC, YEAR(activity_date), MONTH(activity_date)

SELECT activity_user_id, CAST(activity_date AS DATE) activity_day, COUNT(activity_id) activity_count
FROM ikase_reino.cse_activity 
WHERE activity_user_id > 0
GROUP BY activity_user_id, CAST(activity_date AS DATE)
ORDER BY activity_user_id ASC, CAST(activity_date AS DATE) ASC

SELECT activity_user_id, YEAR(activity_date) activity_year, MONTH(activity_date) activity_month, COUNT(activity_id) activity_count
FROM ikase_reino.cse_activity 
WHERE activity_user_id = 75
GROUP BY activity_user_id, YEAR(activity_date), MONTH(activity_date)
ORDER BY YEAR(activity_date), MONTH(activity_date), activity_user_id ASC