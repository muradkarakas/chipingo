session_by_chipingo_email = load 'cql://chipingo/session_by_chipingo_email' using org.apache.cassandra.hadoop.pig.CqlNativeStorage();
session_by_chipingo_email_active = filter session_by_chipingo_email by 
                 (  publish_start_date <= ToUnixTime(CurrentTime()) and publish_end_date >= ToUnixTime(CurrentTime())  );

qtag_replies = load 'cql://chipingo/qtag_replies_by_session' using org.apache.cassandra.hadoop.pig.CqlNativeStorage();

active_replies = cogroup session_by_chipingo_email_active by (chipingo_email, qtag, session_name),                 

qtag_replies by (chipingo_email, qtag, session_name);

qtag_replies_group = group qtag_replies by (chipingo_email,qtag,session_name,option_timestamp);

qtag_replies_group_count = foreach qtag_replies_group generate group, COUNT(qtag_replies) as reply_count;records = foreach qtag_replies_group_count generate          TOTUPLE(              TOTUPLE('chipingo_email', group.chipingo_email),              TOTUPLE('qtag', group.qtag),              TOTUPLE('session_name', group.session_name),              TOTUPLE('option_timestamp', group.option_timestamp)          ),          TOTUPLE(reply_count);store records into 'cql://chipingo/qtag_options?output_query=UPDATE+chipingo.qtag_options+set+reply_count+%3D+%3F' USING org.apache.cassandra.hadoop.pig.CqlNativeStorage();