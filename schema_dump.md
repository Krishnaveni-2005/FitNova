## 4.5 TABLE DESIGN

**1. Client_profiles Table**

| Field Name | Data Type | Description |
|---|---|---|
| profile_id | INT(11) (PK) | Unique ID for client_profile |
| user_id | INT(11) | Foreign Key linking to User |
| gender | VARCHAR(20) | Gender |
| dob | DATE | Dob |
| height_cm | DECIMAL(5,2) | Height cm |
| weight_kg | DECIMAL(5,2) | Weight kg |
| target_weight_kg | DECIMAL(5,2) | Target weight kg |
| primary_goal | VARCHAR(50) | Primary goal |
| activity_level | VARCHAR(50) | Activity level |
| injuries | TEXT | Injuries |
| medical_conditions | TEXT | Medical conditions |
| allergies | TEXT | Allergies |
| sleep_hours_avg | INT(11) | Sleep hours avg |
| diet_preference | VARCHAR(50) | Diet preference |
| water_intake_liters | DECIMAL(3,1) | Water intake liters |
| workout_days_per_week | INT(11) | Workout days per week |
| equipment_access | VARCHAR(50) | Equipment access |
| custom_macros_json | TEXT | Custom macros json |

<br>

**2. Client_progress Table**

| Field Name | Data Type | Description |
|---|---|---|
| progress_id | INT(11) (PK) | Unique ID for client_progre |
| user_id | INT(11) | Foreign Key linking to User |
| log_date | DATE | Timestamp / Date of action |
| current_weight | DECIMAL(5,2) | Current weight |
| status_update | VARCHAR(50) | Timestamp / Date of action |
| notes | TEXT | Notes |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**3. Client_trainer_requests Table**

| Field Name | Data Type | Description |
|---|---|---|
| request_id | INT(11) (PK) | Unique ID for client_trainer_request |
| client_id | INT(11) | Foreign Key linking to User |
| goal | VARCHAR(100) | Goal |
| training_style | VARCHAR(100) | Training style |
| notes | TEXT | Notes |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**4. Email_sent_log Table**

| Field Name | Data Type | Description |
|---|---|---|
| id | INT(11) (PK) | Unique ID for email_sent_log |
| user_id | INT(11) | Foreign Key linking to User |
| email_type | VARCHAR(50) | User's email address |
| sent_at | TIMESTAMP | Timestamp / Date of action |

<br>

**5. Expert_enquiries Table**

| Field Name | Data Type | Description |
|---|---|---|
| enquiry_id | INT(11) (PK) | Unique ID for expert_enquirie |
| name | VARCHAR(100) | Full name of the expert_enquirie |
| phone | VARCHAR(20) | Contact number |
| email | VARCHAR(100) | User's email address |
| reason | TEXT | Reason |
| created_at | TIMESTAMP | Timestamp / Date of action |
| status | ENUM('PENDING','CONTACTED','RESOLVED') | Current status (e.g. Active, Pending) |

<br>

**6. Gamification_badges Table**

| Field Name | Data Type | Description |
|---|---|---|
| badge_id | INT(11) (PK) | Unique ID for gamification_badge |
| name | VARCHAR(50) | Full name of the gamification_badge |
| description | VARCHAR(255) | Description |
| icon_class | VARCHAR(50) | Icon class |
| color | VARCHAR(20) | Color |
| criteria_type | VARCHAR(50) | Criteria type |
| criteria_value | INT(11) | Criteria value |
| created_at | TIMESTAMP | Timestamp / Date of action |
| target_role | ENUM('CLIENT','TRAINER','ALL') | Target role |

<br>

**7. Gym_check_ins Table**

| Field Name | Data Type | Description |
|---|---|---|
| checkin_id | INT(11) (PK) | Unique ID for gym_check_in |
| user_id | INT(11) | Foreign Key linking to User |
| check_in_time | DATETIME | Check in time |

<br>

**8. Gym_equipment Table**

| Field Name | Data Type | Description |
|---|---|---|
| id | INT(11) (PK) | Unique ID for gym_equipment |
| name | VARCHAR(100) | Full name of the gym_equipment |
| total_units | INT(11) | Total units |
| available_units | INT(11) | Available units |
| status | VARCHAR(50) | Current status (e.g. Active, Pending) |
| icon | VARCHAR(50) | Icon |
| color_class | VARCHAR(20) | Color class |
| last_updated | TIMESTAMP | Timestamp / Date of action |

<br>

**9. Gym_settings Table**

| Field Name | Data Type | Description |
|---|---|---|
| setting_id | INT(11) (PK) | Unique ID for gym_setting |
| setting_key | VARCHAR(50) (UNIQUE) | Setting key |
| setting_value | VARCHAR(255) | Setting value |
| updated_at | TIMESTAMP | Timestamp / Date of action |

<br>

**10. Messages Table**

| Field Name | Data Type | Description |
|---|---|---|
| message_id | INT(11) (PK) | Unique ID for message |
| sender_id | INT(11) | Foreign key reference |
| receiver_id | INT(11) | Foreign key reference |
| message_text | TEXT | Message text |
| is_read | TINYINT(1) | Is read |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**11. Password_resets Table**

| Field Name | Data Type | Description |
|---|---|---|
| email | VARCHAR(100) (PK) | Unique ID for password_reset |
| token | VARCHAR(255) | Token |
| expiry | DATETIME | Expiry |

<br>

**12. Payment_receipts Table**

| Field Name | Data Type | Description |
|---|---|---|
| receipt_id | INT(11) (PK) | Unique ID for payment_receipt |
| user_id | INT(11) | Foreign Key linking to User |
| plan_name | VARCHAR(50) | Full name of the payment_receipt |
| billing_cycle | VARCHAR(20) | Billing cycle |
| base_amount | DECIMAL(10,2) | Base amount |
| tax_amount | DECIMAL(10,2) | Tax amount |
| total_amount | DECIMAL(10,2) | Total amount |
| currency | VARCHAR(3) | Currency |
| razorpay_payment_id | VARCHAR(255) | Foreign key reference |
| razorpay_order_id | VARCHAR(255) | Foreign key reference |
| payment_method | VARCHAR(50) | Payment method |
| payment_status | VARCHAR(20) | Current status (e.g. Active, Pending) |
| transaction_date | TIMESTAMP | Timestamp / Date of action |
| receipt_number | VARCHAR(50) (UNIQUE) | Receipt number |
| subscription_start | DATE | Subscription start |
| subscription_end | DATE | Subscription end |

<br>

**13. Payments Table**

| Field Name | Data Type | Description |
|---|---|---|
| payment_id | INT(11) (PK) | Unique ID for payment |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| user_id | INT(11) | Foreign Key linking to User |
| amount | DECIMAL(10,2) | Amount |
| payment_date | DATE | Timestamp / Date of action |
| currency | VARCHAR(3) | Currency |

<br>

**14. Product_reviews Table**

| Field Name | Data Type | Description |
|---|---|---|
| review_id | INT(11) (PK) | Unique ID for product_review |
| product_id | INT(11) | Foreign Key linking to Product |
| user_id | INT(11) | Foreign Key linking to User |
| rating | INT(11) | Rating |
| review_title | VARCHAR(200) | Review title |
| review_text | TEXT | Review text |
| verified_purchase | TINYINT(1) | Verified purchase |
| helpful_count | INT(11) | Helpful count |
| review_images | TEXT | Review images |
| created_at | TIMESTAMP | Timestamp / Date of action |
| updated_at | TIMESTAMP | Timestamp / Date of action |

<br>

**15. Products Table**

| Field Name | Data Type | Description |
|---|---|---|
| product_id | INT(11) (PK) | Unique ID for product |
| name | VARCHAR(255) | Full name of the product |
| category | ENUM('MEN','WOMEN','EQUIPMENT','SUPPLEMENTS') | Category |
| price | DECIMAL(10,2) | Price |
| image_url | VARCHAR(255) | Image url |
| description | TEXT | Description |
| is_new | TINYINT(1) | Is new |
| is_sale | TINYINT(1) | Is sale |
| is_bestseller | TINYINT(1) | Is bestseller |
| has_sizes | TINYINT(1) | Has sizes |
| created_at | TIMESTAMP | Timestamp / Date of action |
| rating | DECIMAL(2,1) | Rating |
| review_count | INT(11) | Review count |
| stock_quantity | INT(11) | Stock quantity |
| stock | INT(11) | Stock |

<br>

**16. Review_helpful Table**

| Field Name | Data Type | Description |
|---|---|---|
| id | INT(11) (PK) | Unique ID for review_helpful |
| review_id | INT(11) | Foreign key reference |
| user_id | INT(11) | Foreign Key linking to User |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**17. Session_requests Table**

| Field Name | Data Type | Description |
|---|---|---|
| request_id | INT(11) (PK) | Unique ID for session_request |
| user_id | INT(11) | Foreign Key linking to User |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| status | ENUM('PENDING','APPROVED','REJECTED') | Current status (e.g. Active, Pending) |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**18. Shop_order_items Table**

| Field Name | Data Type | Description |
|---|---|---|
| item_id | INT(11) (PK) | Unique ID for shop_order_item |
| order_id | INT(11) | Foreign key reference |
| product_id | INT(11) | Foreign Key linking to Product |
| product_name | VARCHAR(255) | Full name of the shop_order_item |
| quantity | INT(11) | Quantity |
| price | DECIMAL(10,2) | Price |
| size | VARCHAR(20) | Size |
| image_url | VARCHAR(255) | Image url |

<br>

**19. Shop_orders Table**

| Field Name | Data Type | Description |
|---|---|---|
| order_id | INT(11) (PK) | Unique ID for shop_order |
| user_id | INT(11) | Foreign Key linking to User |
| total_amount | DECIMAL(10,2) | Total amount |
| address | TEXT | Address |
| city | VARCHAR(100) | City |
| zip | VARCHAR(20) | Zip |
| payment_method | VARCHAR(50) | Payment method |
| order_status | VARCHAR(50) | Current status (e.g. Active, Pending) |
| order_date | TIMESTAMP | Timestamp / Date of action |
| delivery_date | VARCHAR(50) | Timestamp / Date of action |
| return_reason | TEXT | Return reason |
| admin_message | TEXT | Admin message |

<br>

**20. Subscription_plans Table**

| Field Name | Data Type | Description |
|---|---|---|
| plan_id | INT(11) (PK) | Unique ID for subscription_plan |
| name | VARCHAR(50) | Full name of the subscription_plan |
| price_monthly | DECIMAL(10,2) | Price monthly |
| price_yearly | DECIMAL(10,2) | Price yearly |
| description | VARCHAR(255) | Description |
| features | TEXT | Features |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**21. Trainer_achievements Table**

| Field Name | Data Type | Description |
|---|---|---|
| id | INT(11) (PK) | Unique ID for trainer_achievement |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| title | VARCHAR(255) | Title |
| issuer | VARCHAR(255) | Issuer |
| date_earned | DATE | Timestamp / Date of action |
| image_url | VARCHAR(255) | Image url |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**22. Trainer_applications Table**

| Field Name | Data Type | Description |
|---|---|---|
| application_id | INT(11) (PK) | Unique ID for trainer_application |
| client_id | INT(11) | Foreign Key linking to User |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| status | ENUM('PENDING','APPROVED','REJECTED','ADMIN_SUGGESTED') | Current status (e.g. Active, Pending) |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**23. Trainer_attendance Table**

| Field Name | Data Type | Description |
|---|---|---|
| id | INT(11) (PK) | Unique ID for trainer_attendance |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| check_in_time | DATETIME | Check in time |
| check_out_time | DATETIME | Check out time |
| zone | VARCHAR(100) | Zone |
| status | ENUM('CHECKED_IN','CHECKED_OUT') | Current status (e.g. Active, Pending) |

<br>

**24. Trainer_diet_plans Table**

| Field Name | Data Type | Description |
|---|---|---|
| diet_id | INT(11) (PK) | Unique ID for trainer_diet_plan |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| user_id | INT(11) | Foreign Key linking to User |
| plan_name | VARCHAR(255) | Full name of the trainer_diet_plan |
| client_name | VARCHAR(255) | Full name of the trainer_diet_plan |
| target_calories | INT(11) | Target calories |
| diet_type | VARCHAR(100) | Diet type |
| meal_details | TEXT | Meal details |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**25. Trainer_ratings Table**

| Field Name | Data Type | Description |
|---|---|---|
| rating_id | INT(11) (PK) | Unique ID for trainer_rating |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| client_id | INT(11) | Foreign Key linking to User |
| rating | DECIMAL(2,1) | Rating |
| review | TEXT | Review |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**26. Trainer_reviews Table**

| Field Name | Data Type | Description |
|---|---|---|
| review_id | INT(11) (PK) | Unique ID for trainer_review |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| client_id | INT(11) | Foreign Key linking to User |
| rating | INT(11) | Rating |
| review_text | TEXT | Review text |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**27. Trainer_schedules Table**

| Field Name | Data Type | Description |
|---|---|---|
| schedule_id | INT(11) (PK) | Unique ID for trainer_schedule |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| client_name | VARCHAR(255) | Full name of the trainer_schedule |
| session_time | TIME | Session time |
| session_date | DATE | Timestamp / Date of action |
| session_type | VARCHAR(100) | Session type |
| status | VARCHAR(50) | Current status (e.g. Active, Pending) |
| meeting_link | VARCHAR(500) | Meeting link |

<br>

**28. Trainer_workouts Table**

| Field Name | Data Type | Description |
|---|---|---|
| workout_id | INT(11) (PK) | Unique ID for trainer_workout |
| trainer_id | INT(11) | Foreign Key linking to Trainer |
| user_id | INT(11) | Foreign Key linking to User |
| plan_name | VARCHAR(255) | Full name of the trainer_workout |
| client_name | VARCHAR(255) | Full name of the trainer_workout |
| duration_weeks | INT(11) | Duration weeks |
| exercises | TEXT | Exercises |
| difficulty | VARCHAR(50) | Difficulty |
| created_at | TIMESTAMP | Timestamp / Date of action |
| days_per_week | INT(11) | Days per week |

<br>

**29. User_activity_log Table**

| Field Name | Data Type | Description |
|---|---|---|
| log_id | INT(11) (PK) | Unique ID for user_activity_log |
| user_id | INT(11) | Foreign Key linking to User |
| activity_type | VARCHAR(50) | Activity type |
| activity_name | VARCHAR(100) | Full name of the user_activity_log |
| duration_minutes | INT(11) | Duration minutes |
| calories_burned | INT(11) | Calories burned |
| activity_date | DATE | Timestamp / Date of action |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**30. User_activity_logs Table**

| Field Name | Data Type | Description |
|---|---|---|
| log_id | INT(11) (PK) | Unique ID for user_activity_log |
| user_id | INT(11) | Foreign Key linking to User |
| activity_type | VARCHAR(50) | Activity type |
| duration_minutes | INT(11) | Duration minutes |
| calories_burned | INT(11) | Calories burned |
| log_date | DATE | Timestamp / Date of action |
| created_at | TIMESTAMP | Timestamp / Date of action |

<br>

**31. User_badges Table**

| Field Name | Data Type | Description |
|---|---|---|
| id | INT(11) (PK) | Unique ID for user_badge |
| user_id | INT(11) | Foreign Key linking to User |
| badge_id | INT(11) | Foreign key reference |
| earned_at | TIMESTAMP | Timestamp / Date of action |

<br>

**32. User_gamification_stats Table**

| Field Name | Data Type | Description |
|---|---|---|
| user_id | INT(11) (PK) | Unique ID for user_gamification_stat |
| total_points | INT(11) | Total points |
| current_streak | INT(11) | Current streak |
| last_login_date | DATE | Timestamp / Date of action |
| updated_at | TIMESTAMP | Timestamp / Date of action |
| completed_workouts | INT(11) | Completed workouts |
| total_calories | INT(11) | Total calories |

<br>

**33. User_notifications Table**

| Field Name | Data Type | Description |
|---|---|---|
| notification_id | INT(11) (PK) | Unique ID for user_notification |
| user_id | INT(11) | Foreign Key linking to User |
| notification_type | VARCHAR(50) | Notification type |
| message | TEXT | Message |
| created_at | TIMESTAMP | Timestamp / Date of action |
| is_read | TINYINT(1) | Is read |

<br>

**34. User_subscriptions Table**

| Field Name | Data Type | Description |
|---|---|---|
| subscription_id | INT(11) (PK) | Unique ID for user_subscription |
| user_id | INT(11) (UNIQUE) | Foreign Key linking to User |
| current_plan | VARCHAR(50) | Current plan |
| billing_cycle | VARCHAR(20) | Billing cycle |
| subscription_start | DATE | Subscription start |
| subscription_end | DATE | Subscription end |
| can_switch_after | DATE | Can switch after |
| last_payment_id | INT(11) | Foreign key reference |
| created_at | TIMESTAMP | Timestamp / Date of action |
| updated_at | TIMESTAMP | Timestamp / Date of action |

<br>

**35. Users Table**

| Field Name | Data Type | Description |
|---|---|---|
| user_id | INT(11) (PK) | Unique ID for user |
| first_name | VARCHAR(50) | Full name of the user |
| last_name | VARCHAR(50) | Full name of the user |
| email | VARCHAR(100) (UNIQUE) | User's email address |
| phone | VARCHAR(20) | Contact number |
| password_hash | VARCHAR(255) | Encrypted password |
| role | ENUM('FREE','PRO','TRAINER','ADMIN','ELITE','LITE') | Role |
| auth_provider | ENUM('LOCAL','GOOGLE','FACEBOOK') | Foreign key reference |
| oauth_provider_id | VARCHAR(255) | Foreign key reference |
| is_email_verified | TINYINT(1) | User's email address |
| account_status | ENUM('ACTIVE','INACTIVE','SUSPENDED','PENDING') | Current status (e.g. Active, Pending) |
| trainer_specialization | VARCHAR(100) | Trainer specialization |
| trainer_experience | INT(11) | Trainer experience |
| trainer_certification | VARCHAR(255) | Trainer certification |
| assigned_trainer_id | INT(11) | Foreign Key linking to Trainer |
| created_at | TIMESTAMP | Timestamp / Date of action |
| updated_at | TIMESTAMP | Timestamp / Date of action |
| assignment_status | ENUM('NONE','PENDING','APPROVED','REJECTED','TRAINER_INVITE','LOOKING_FOR_TRAINER') | Current status (e.g. Active, Pending) |
| gym_membership_status | ENUM('ACTIVE','INACTIVE') | Current status (e.g. Active, Pending) |
| profile_picture | VARCHAR(255) | Profile picture |
| bio | TEXT | Bio |
| trainer_type | ENUM('ONLINE','OFFLINE') | Trainer type |

<br>

