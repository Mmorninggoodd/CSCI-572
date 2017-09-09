import java.io.IOException;
import java.util.StringTokenizer;
import java.util.HashMap; 
import java.util.*; 

import org.apache.hadoop.conf.Configuration;
import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.IntWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;
import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;
import org.apache.hadoop.mapreduce.lib.input.FileSplit;

public class InvertedIndex {

  public static class ShreyMapper
       extends Mapper<Object, Text, Text, Text>{

    private Text word = new Text();
    private final static Text location = new Text(); 

    public void map(Object key, Text value, Context context
                    ) throws IOException, InterruptedException {
      
      StringTokenizer itr = new StringTokenizer(value.toString());
      
      FileSplit filesplit = (FileSplit) context.getInputSplit();
      String filename = filesplit.getPath().getName(); 
      filename = filename.substring(0, filename.length()-4); 
      location.set(filename); 

      while (itr.hasMoreTokens()) {
        word.set(itr.nextToken());
        context.write(word, location);
      }
    }
  }

  public static class ShreyReducer
       extends Reducer<Text,Text,Text,Text> {
    
    private String ans = ""; 

    public void reduce(Text key, Iterable<Text> values,
                       Context context
                       ) throws IOException, InterruptedException {
      
      HashMap hmap = new HashMap();
      int counter = 0; 
      for(Text t : values){
        String stringIndex = t.toString();
        if(hmap != null && hmap.get(stringIndex)!=null){
          counter=(int)hmap.get(stringIndex);
          hmap.put(stringIndex, ++counter);
        }else{
          hmap.put(stringIndex,1); 
        }
      }
      
      String ans = hmap.toString(); 
      ans = ans.replace("{","");
      ans = ans.replace("}","");
      ans = ans.replace('=',':');
      ans = ans.replace(",","");
      ans = ans.replace(" ","	");

	  context.write(key, new Text(ans)); 
    }
  }

  public static void main(String[] args) throws Exception {
    Configuration conf = new Configuration();
    Job job = Job.getInstance(conf, "Inverted Index");
    job.setJarByClass(InvertedIndex.class);
    job.setMapperClass(ShreyMapper.class);
    job.setReducerClass(ShreyReducer.class);
    job.setOutputKeyClass(Text.class);
    job.setOutputValueClass(Text.class);
    FileInputFormat.addInputPath(job, new Path(args[0]));
    FileOutputFormat.setOutputPath(job, new Path(args[1]));
    System.exit(job.waitForCompletion(true) ? 0 : 1);
  }
}