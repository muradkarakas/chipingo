
session_by_chipingo_email = load 'cql://chipingo/session_by_chipingo_email' using org.apache.cassandra.hadoop.pig.CqlNativeStorage();

session_by_chipingo_email_active = filter session_by_chipingo_email 
                                   by (
                                       publish_start_date <= ToUnixTime(CurrentTime()) and
                                       publish_end_date >= ToUnixTime(CurrentTime()) 
                                   );

dump session_by_chipingo_email_active;